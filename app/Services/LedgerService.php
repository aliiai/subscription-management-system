<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use RuntimeException;

class LedgerService
{
    public const CASH = '1000';

    public const ACCOUNTS_RECEIVABLE = '1100';

    public const DEFERRED_REVENUE = '2000';

    public const SUBSCRIPTION_REVENUE = '4000';

    /**
     * Definitions for the seeded chart of accounts.
     *
     * @var array<string, array{name: string, type: AccountType}>
     */
    protected const DEFINITIONS = [
        self::CASH => ['name' => 'النقدية', 'type' => AccountType::Asset],
        self::ACCOUNTS_RECEIVABLE => ['name' => 'الذمم المدينة (العملاء)', 'type' => AccountType::Asset],
        self::DEFERRED_REVENUE => ['name' => 'الإيرادات المؤجلة', 'type' => AccountType::Liability],
        self::SUBSCRIPTION_REVENUE => ['name' => 'إيرادات الاشتراكات', 'type' => AccountType::Revenue],
    ];

    /**
     * Ensure the tenant has its base chart of accounts.
     */
    public function seedChartOfAccounts(Tenant $tenant): void
    {
        foreach (array_keys(self::DEFINITIONS) as $code) {
            $this->accountFor($tenant, $code);
        }
    }

    /**
     * Resolve (creating if needed) a tenant account by its code.
     */
    public function accountFor(Tenant $tenant, string $code): Account
    {
        $definition = self::DEFINITIONS[$code] ?? throw new RuntimeException("Unknown account code: {$code}");

        return $tenant->accounts()->firstOrCreate(
            ['code' => $code],
            [
                'name' => $definition['name'],
                'type' => $definition['type'],
                'normal_balance' => $definition['type']->normalBalance(),
            ],
        );
    }

    /**
     * Post a balanced journal entry. Each line is [code, debit, credit].
     *
     * @param  array<int, array{code: string, debit?: float|int|string, credit?: float|int|string}>  $lines
     */
    public function post(
        Tenant $tenant,
        Carbon|string $date,
        string $description,
        array $lines,
        ?Model $source = null,
        ?string $reference = null,
    ): JournalEntry {
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($lines as $line) {
            $totalDebit += (float) ($line['debit'] ?? 0);
            $totalCredit += (float) ($line['credit'] ?? 0);
        }

        if (round($totalDebit, 2) !== round($totalCredit, 2)) {
            throw new RuntimeException('Journal entry is not balanced.');
        }

        if (round($totalDebit, 2) <= 0) {
            throw new RuntimeException('Journal entry has no amounts.');
        }

        $entry = $tenant->journalEntries()->create([
            'entry_date' => $date instanceof Carbon ? $date : Carbon::parse($date),
            'description' => $description,
            'reference' => $reference,
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
        ]);

        foreach ($lines as $line) {
            $entry->lines()->create([
                'account_id' => $this->accountFor($tenant, $line['code'])->id,
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
            ]);
        }

        return $entry;
    }

    /**
     * Record an issued invoice: Dr Accounts Receivable / Cr Deferred Revenue.
     */
    public function recordInvoiceIssued(Invoice $invoice): JournalEntry
    {
        return $this->post(
            $invoice->tenant,
            $invoice->issue_date,
            "إصدار الفاتورة {$invoice->invoice_number}",
            [
                ['code' => self::ACCOUNTS_RECEIVABLE, 'debit' => $invoice->amount],
                ['code' => self::DEFERRED_REVENUE, 'credit' => $invoice->amount],
            ],
            $invoice,
            $invoice->invoice_number,
        );
    }

    /**
     * Record revenue recognition: Dr Deferred Revenue / Cr Subscription Revenue.
     */
    public function recordRevenueRecognized(Invoice $invoice): JournalEntry
    {
        return $this->post(
            $invoice->tenant,
            now(),
            "الاعتراف بإيراد الفاتورة {$invoice->invoice_number}",
            [
                ['code' => self::DEFERRED_REVENUE, 'debit' => $invoice->amount],
                ['code' => self::SUBSCRIPTION_REVENUE, 'credit' => $invoice->amount],
            ],
            $invoice,
            $invoice->invoice_number,
        );
    }

    /**
     * Record a received payment: Dr Cash / Cr Accounts Receivable.
     */
    public function recordPaymentReceived(Payment $payment): JournalEntry
    {
        return $this->post(
            $payment->tenant,
            $payment->paid_at,
            "تحصيل دفعة على الفاتورة {$payment->invoice->invoice_number}",
            [
                ['code' => self::CASH, 'debit' => $payment->amount],
                ['code' => self::ACCOUNTS_RECEIVABLE, 'credit' => $payment->amount],
            ],
            $payment,
            $payment->invoice->invoice_number,
        );
    }

    /**
     * Post reversing entries for every journal entry linked to the given source.
     */
    public function reverse(Model $source, string $description): void
    {
        $tenant = $source->tenant;

        $source->journalEntries()->with('lines.account')->get()->each(function (JournalEntry $entry) use ($tenant, $description, $source) {
            $lines = $entry->lines->map(fn ($line) => [
                'code' => $line->account->code,
                'debit' => (float) $line->credit,
                'credit' => (float) $line->debit,
            ])->all();

            $this->post($tenant, now(), $description, $lines, $source, $entry->reference);
        });
    }
}
