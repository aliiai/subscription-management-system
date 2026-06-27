<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RecognitionService
{
    public function __construct(protected LedgerService $ledger) {}

    /**
     * Recognize revenue for all eligible invoices of a tenant for the given period.
     *
     * Idempotent: an invoice already recognized (revenue_recognized_at set) is skipped.
     * Recognition is independent of payment status and ignores voided invoices.
     *
     * @return array{count: int, amount: float}
     */
    public function recognizeForTenant(Tenant $tenant, Carbon $period): array
    {
        $count = 0;
        $amount = 0.0;

        $tenant->invoices()
            ->eligibleForRecognition($period)
            ->get()
            ->each(function (Invoice $invoice) use (&$count, &$amount) {
                DB::transaction(function () use ($invoice) {
                    $this->ledger->recordRevenueRecognized($invoice);
                    $invoice->update(['revenue_recognized_at' => now()]);
                });

                $count++;
                $amount += (float) $invoice->amount;
            });

        return ['count' => $count, 'amount' => round($amount, 2)];
    }
}
