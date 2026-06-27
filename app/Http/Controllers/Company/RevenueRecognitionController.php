<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Services\LedgerService;
use App\Services\NotificationService;
use App\Services\RecognitionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class RevenueRecognitionController extends Controller
{
    public function __construct(
        protected LedgerService $ledger,
        protected RecognitionService $recognition,
        protected NotificationService $notifications,
    ) {}

    /**
     * Display the revenue recognition workspace with KPIs and invoice lists.
     */
    public function index(Request $request): View
    {
        $tenant = $this->tenant($request);

        $month = $request->string('month')->value();
        $period = $month && preg_match('/^\d{4}-\d{2}$/', $month)
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();

        $view = $request->string('view')->value() === 'recognized' ? 'recognized' : 'pending';

        $invoices = $this->listQuery($tenant, $period, $view, $request)->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('company.revenue-recognition._results', [
                'invoices' => $invoices,
                'view' => $view,
                'period' => $period,
                'filters' => $this->filters($request),
            ]);
        }

        $pendingBase = $tenant->invoices()->eligibleForRecognition($period);
        $recognizedThisMonth = $tenant->invoices()
            ->whereNotNull('revenue_recognized_at')
            ->whereBetween('revenue_recognized_at', [$period->copy()->startOfMonth(), $period->copy()->endOfMonth()]);

        return view('company.revenue-recognition.index', [
            'period' => $period,
            'view' => $view,
            'invoices' => $invoices,
            'filters' => $this->filters($request),
            'kpis' => [
                'deferred_balance' => $this->ledger->accountFor($tenant, LedgerService::DEFERRED_REVENUE)->balance(),
                'recognized_total' => $this->ledger->accountFor($tenant, LedgerService::SUBSCRIPTION_REVENUE)->balance(),
                'pending_count' => (clone $pendingBase)->count(),
                'pending_amount' => (float) (clone $pendingBase)->sum('amount'),
                'recognized_count' => $tenant->invoices()->whereNotNull('revenue_recognized_at')->count(),
                'recognized_month_count' => (clone $recognizedThisMonth)->count(),
                'recognized_month_amount' => (float) (clone $recognizedThisMonth)->sum('amount'),
            ],
        ]);
    }

    /**
     * Build the invoice list query for the active tab (pending / recognized) and search.
     *
     * @return Builder<Invoice>
     */
    protected function listQuery(Tenant $tenant, Carbon $period, string $view, Request $request)
    {
        $query = $view === 'recognized'
            ? $tenant->invoices()->whereNotNull('revenue_recognized_at')->latest('revenue_recognized_at')->latest('id')
            : $tenant->invoices()->eligibleForRecognition($period)->orderBy('period_end')->orderBy('id');

        return $query
            ->with('customer')
            ->when($request->filled('q'), function ($inner) use ($request) {
                $search = $request->string('q')->value();
                $inner->where(function ($w) use ($search) {
                    $w->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            });
    }

    /**
     * Normalize the active filters for the views.
     *
     * @return array{q: string, view: string, month: string}
     */
    protected function filters(Request $request): array
    {
        return [
            'q' => $request->string('q')->value(),
            'view' => $request->string('view')->value(),
            'month' => $request->string('month')->value(),
        ];
    }

    /**
     * Recognize revenue for all eligible invoices in the selected month.
     */
    public function recognize(Request $request): RedirectResponse
    {
        $validated = $request->validate(['month' => ['nullable', 'date_format:Y-m']]);

        $tenant = $this->tenant($request);

        $period = isset($validated['month'])
            ? Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth()
            : now()->startOfMonth();

        $result = $this->recognition->recognizeForTenant($tenant, $period);

        if ($result['count'] > 0) {
            $this->notifications->revenueRecognized($tenant, $result['count'], $result['amount'], $period);
        }

        $message = $result['count'] > 0
            ? "تم الاعتراف بإيراد {$result['count']} فاتورة بإجمالي ".number_format($result['amount'], 2)." لفترة {$period->format('Y/m')}."
            : 'لا توجد فواتير مؤهلة للاعتراف بالإيراد لهذه الفترة.';

        return redirect()
            ->route('company.revenue-recognition', ['month' => $period->format('Y-m')])
            ->with('status', $message);
    }

    /**
     * Resolve the authenticated user's tenant, ensuring one exists.
     */
    protected function tenant(Request $request): Tenant
    {
        return $request->user()->tenant ?? abort(403);
    }
}
