<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function __construct(protected LedgerService $ledger) {}

    /**
     * Generate monthly invoices for all active subscriptions of a tenant for the given period.
     * Idempotent: a subscription already invoiced for the period is skipped.
     */
    public function generateForTenant(Tenant $tenant, Carbon $period): int
    {
        $periodStart = $period->copy()->startOfMonth();
        $periodEnd = $period->copy()->endOfMonth();
        $created = 0;

        $tenant->subscriptions()
            ->where('status', SubscriptionStatus::Active)
            ->with(['plan', 'customer'])
            ->get()
            ->each(function (Subscription $subscription) use ($tenant, $periodStart, $periodEnd, &$created) {
                $alreadyInvoiced = $tenant->invoices()
                    ->where('subscription_id', $subscription->id)
                    ->whereDate('period_start', $periodStart)
                    ->exists();

                if ($alreadyInvoiced) {
                    return;
                }

                $amount = (float) ($subscription->price ?? $subscription->plan?->price ?? 0);

                if ($amount <= 0) {
                    return;
                }

                DB::transaction(function () use ($tenant, $subscription, $periodStart, $periodEnd, $amount) {
                    $invoice = $tenant->invoices()->create([
                        'customer_id' => $subscription->customer_id,
                        'subscription_id' => $subscription->id,
                        'invoice_number' => Invoice::nextNumberFor($tenant),
                        'issue_date' => $periodStart,
                        'due_date' => $periodStart->copy()->addDays(14),
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'amount' => $amount,
                        'amount_paid' => 0,
                        'currency' => $subscription->plan?->currency ?? 'SAR',
                        'status' => InvoiceStatus::Unpaid,
                    ]);

                    $this->ledger->recordInvoiceIssued($invoice);
                });

                $created++;
            });

        return $created;
    }
}
