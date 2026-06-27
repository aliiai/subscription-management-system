<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(protected ReportService $reports) {}

    /**
     * Build the full set of dashboard data for a tenant.
     *
     * @return array<string, mixed>
     */
    public function forTenant(Tenant $tenant): array
    {
        $now = now();
        $balanceSheet = $this->reports->balanceSheet($tenant, $now->copy()->endOfDay());
        $balances = $this->balancesByCode($balanceSheet);

        $cash = $balances[LedgerService::CASH] ?? 0.0;
        $receivable = $balances[LedgerService::ACCOUNTS_RECEIVABLE] ?? 0.0;
        $deferred = $balances[LedgerService::DEFERRED_REVENUE] ?? 0.0;

        $recognizedAllTime = $this->reports->incomeStatement(
            $tenant,
            Carbon::create(2000, 1, 1)->startOfDay(),
            $now->copy()->endOfDay(),
        )['total_revenue'];

        $recognizedThisMonth = $this->reports->incomeStatement(
            $tenant,
            $now->copy()->startOfMonth(),
            $now->copy()->endOfDay(),
        )['total_revenue'];

        $recognizedLastMonth = $this->reports->incomeStatement(
            $tenant,
            $now->copy()->subMonthNoOverflow()->startOfMonth(),
            $now->copy()->subMonthNoOverflow()->endOfMonth(),
        )['total_revenue'];

        $collectedThisMonth = (float) $tenant->payments()
            ->whereBetween('paid_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('amount');

        $collectedLastMonth = (float) $tenant->payments()
            ->whereBetween('paid_at', [
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
            ])->sum('amount');

        $activeSubscriptions = $tenant->subscriptions()
            ->where('status', SubscriptionStatus::Active)
            ->count();

        $mrr = (float) $tenant->subscriptions()
            ->where('status', SubscriptionStatus::Active)
            ->sum('price');

        $openStatuses = [InvoiceStatus::Unpaid->value, InvoiceStatus::PartiallyPaid->value];

        $unpaidCount = $tenant->invoices()->whereIn('status', $openStatuses)->count();

        return [
            'symbol' => 'ر.س',
            'kpis' => [
                'mrr' => [
                    'value' => $mrr,
                    'subtitle' => $activeSubscriptions.' اشتراك نشط',
                ],
                'recognized' => [
                    'value' => $recognizedThisMonth,
                    'delta' => $this->changePercent($recognizedThisMonth, $recognizedLastMonth),
                ],
                'cash' => [
                    'value' => $cash,
                    'subtitle' => 'محصّل هذا الشهر '.number_format($collectedThisMonth, 0),
                    'delta' => $this->changePercent($collectedThisMonth, $collectedLastMonth),
                ],
                'receivable' => [
                    'value' => $receivable,
                    'subtitle' => $unpaidCount.' فاتورة مفتوحة',
                ],
            ],
            'charts' => [
                'revenueTrend' => $this->revenueTrend($tenant, $now),
                'deferredVsRecognized' => [
                    'deferred' => round($deferred, 2),
                    'recognized' => round($recognizedAllTime, 2),
                ],
                'plans' => $this->plansDistribution($tenant),
                'invoiceStatus' => $this->invoiceStatusBreakdown($tenant),
            ],
            'recentInvoices' => $tenant->invoices()
                ->with('customer')
                ->latest('issue_date')->latest('id')
                ->take(5)->get(),
            'recentPayments' => $tenant->payments()
                ->with(['customer', 'invoice'])
                ->latest('paid_at')->latest('id')
                ->take(5)->get(),
            'alerts' => [
                'overdue' => $tenant->invoices()
                    ->whereIn('status', $openStatuses)
                    ->whereDate('due_date', '<', $now)
                    ->with('customer')
                    ->orderBy('due_date')
                    ->take(5)->get(),
                'overdueCount' => $tenant->invoices()
                    ->whereIn('status', $openStatuses)
                    ->whereDate('due_date', '<', $now)->count(),
                'unrecognizedCount' => $tenant->invoices()
                    ->whereNull('revenue_recognized_at')
                    ->where('status', '!=', InvoiceStatus::Void)
                    ->where('amount', '>', 0)
                    ->whereDate('period_end', '<=', $now)
                    ->count(),
            ],
            'health' => [
                'balanced' => $balanceSheet['balanced'],
                'totalAssets' => $balanceSheet['total_assets'],
                'journalEntries' => $tenant->journalEntries()->count(),
                'lastRecognitionAt' => $tenant->invoices()->max('revenue_recognized_at'),
                'cash' => round($cash, 2),
                'receivable' => round($receivable, 2),
                'deferred' => round($deferred, 2),
                'recognized' => round($recognizedAllTime, 2),
            ],
        ];
    }

    /**
     * Recognized revenue vs collected cash over the last 6 months.
     *
     * @return array{labels: array<int, string>, recognized: array<int, float>, collected: array<int, float>}
     */
    protected function revenueTrend(Tenant $tenant, Carbon $now): array
    {
        $months = [' يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];

        $labels = [];
        $recognized = [];
        $collected = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonthsNoOverflow($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $labels[] = $months[$month->month - 1].' '.$month->format('y');

            $recognized[] = round($this->reports->incomeStatement($tenant, $start, $end)['total_revenue'], 2);
            $collected[] = round((float) $tenant->payments()->whereBetween('paid_at', [$start, $end])->sum('amount'), 2);
        }

        return [
            'labels' => $labels,
            'recognized' => $recognized,
            'collected' => $collected,
        ];
    }

    /**
     * Active subscription MRR contribution grouped by plan.
     *
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    protected function plansDistribution(Tenant $tenant): array
    {
        $grouped = $tenant->subscriptions()
            ->where('status', SubscriptionStatus::Active)
            ->with('plan')
            ->get()
            ->groupBy(fn ($subscription) => $subscription->plan?->name ?? 'بدون خطة')
            ->map(fn (Collection $group) => round((float) $group->sum('price'), 2));

        return [
            'labels' => $grouped->keys()->all(),
            'values' => $grouped->values()->all(),
        ];
    }

    /**
     * Count of invoices per status.
     *
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    protected function invoiceStatusBreakdown(Tenant $tenant): array
    {
        $counts = $tenant->invoices()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statuses = [
            InvoiceStatus::Paid,
            InvoiceStatus::PartiallyPaid,
            InvoiceStatus::Unpaid,
            InvoiceStatus::Void,
        ];

        $labels = [];
        $values = [];

        foreach ($statuses as $status) {
            $labels[] = $status->label();
            $values[] = (int) ($counts[$status->value] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Flatten balance sheet lines into a code => balance map.
     *
     * @param  array<string, mixed>  $balanceSheet
     * @return array<string, float>
     */
    protected function balancesByCode(array $balanceSheet): array
    {
        return collect($balanceSheet['asset_lines'])
            ->merge($balanceSheet['liability_lines'])
            ->merge($balanceSheet['equity_lines'])
            ->mapWithKeys(fn ($line) => [$line['code'] => (float) $line['balance']])
            ->all();
    }

    /**
     * Percentage change between two periods, or null when not comparable.
     */
    protected function changePercent(float $current, float $previous): ?float
    {
        if ($previous <= 0.0) {
            return $current > 0.0 ? 100.0 : null;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }
}
