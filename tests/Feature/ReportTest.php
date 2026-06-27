<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LedgerService;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function ledger(): LedgerService
    {
        return app(LedgerService::class);
    }

    protected function reports(): ReportService
    {
        return app(ReportService::class);
    }

    /**
     * Post a revenue recognition entry (Dr Deferred Revenue / Cr Subscription Revenue) on a date.
     */
    protected function recognizeRevenue(Tenant $tenant, Carbon $date, float $amount): void
    {
        $this->ledger()->post($tenant, $date, 'اعتراف بإيراد', [
            ['code' => LedgerService::DEFERRED_REVENUE, 'debit' => $amount],
            ['code' => LedgerService::SUBSCRIPTION_REVENUE, 'credit' => $amount],
        ]);
    }

    public function test_income_statement_sums_revenue_within_period_only(): void
    {
        $tenant = Tenant::factory()->create();
        $this->recognizeRevenue($tenant, Carbon::parse('2026-02-15'), 100);
        $this->recognizeRevenue($tenant, Carbon::parse('2026-03-15'), 250);

        $february = $this->reports()->incomeStatement($tenant, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-28'));
        $this->assertEqualsWithDelta(100, $february['total_revenue'], 0.001);
        $this->assertEqualsWithDelta(100, $february['net_income'], 0.001);

        $bothMonths = $this->reports()->incomeStatement($tenant, Carbon::parse('2026-02-01'), Carbon::parse('2026-03-31'));
        $this->assertEqualsWithDelta(350, $bothMonths['total_revenue'], 0.001);
    }

    public function test_balance_sheet_is_balanced_after_full_cycle(): void
    {
        $tenant = Tenant::factory()->create();
        $customer = Customer::factory()->for($tenant)->create();

        $invoice = Invoice::factory()->for($tenant)->create([
            'customer_id' => $customer->id,
            'amount' => 100,
            'amount_paid' => 0,
            'status' => InvoiceStatus::Unpaid,
        ]);

        $this->ledger()->recordInvoiceIssued($invoice);
        $this->recognizeRevenue($tenant, now(), 100);

        $payment = $tenant->payments()->create([
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'amount' => 100,
            'paid_at' => now(),
            'method' => 'cash',
        ]);
        $this->ledger()->recordPaymentReceived($payment);

        $report = $this->reports()->balanceSheet($tenant, now());

        $this->assertTrue($report['balanced']);
        $this->assertEqualsWithDelta(100, $report['total_assets'], 0.001);
        $this->assertEqualsWithDelta(0, $report['total_liabilities'], 0.001);
        $this->assertEqualsWithDelta(100, $report['total_equity'], 0.001);
        $this->assertEqualsWithDelta($report['total_assets'], $report['total_liabilities_equity'], 0.001);
    }

    public function test_balance_sheet_as_of_excludes_later_entries(): void
    {
        $tenant = Tenant::factory()->create();

        // Invoice issuance posted in February.
        $this->ledger()->post($tenant, Carbon::parse('2026-02-01'), 'فاتورة', [
            ['code' => LedgerService::ACCOUNTS_RECEIVABLE, 'debit' => 100],
            ['code' => LedgerService::DEFERRED_REVENUE, 'credit' => 100],
        ]);

        // A later entry that must be excluded when reporting as of mid-February.
        $this->ledger()->post($tenant, Carbon::parse('2026-04-01'), 'فاتورة لاحقة', [
            ['code' => LedgerService::ACCOUNTS_RECEIVABLE, 'debit' => 500],
            ['code' => LedgerService::DEFERRED_REVENUE, 'credit' => 500],
        ]);

        $report = $this->reports()->balanceSheet($tenant, Carbon::parse('2026-02-15'));

        $this->assertEqualsWithDelta(100, $report['total_assets'], 0.001);
        $this->assertEqualsWithDelta(100, $report['total_liabilities'], 0.001);
        $this->assertTrue($report['balanced']);
    }

    public function test_reports_are_tenant_isolated(): void
    {
        $tenant = Tenant::factory()->create();
        $other = Tenant::factory()->create();
        $this->recognizeRevenue($other, Carbon::parse('2026-02-15'), 999);

        $report = $this->reports()->incomeStatement($tenant, Carbon::parse('2026-02-01'), Carbon::parse('2026-02-28'));

        $this->assertEqualsWithDelta(0, $report['total_revenue'], 0.001);
    }

    public function test_income_statement_page_renders_recognized_revenue(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $this->recognizeRevenue($tenant, now(), 100);

        $this->actingAs($user)
            ->get(route('company.income-statement', [
                'from' => now()->startOfMonth()->format('Y-m-d'),
                'to' => now()->format('Y-m-d'),
            ]))
            ->assertOk()
            ->assertSee('قائمة الدخل')
            ->assertSee('100.00');
    }

    public function test_balance_sheet_page_shows_balanced_state(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $this->ledger()->seedChartOfAccounts($tenant);

        $this->actingAs($user)
            ->get(route('company.balance-sheet'))
            ->assertOk()
            ->assertSee('الميزانية العمومية')
            ->assertSee('متوازنة');
    }
}
