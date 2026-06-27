<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\InvoiceResource;
use App\Services\LedgerService;
use App\Services\NotificationService;
use App\Services\RecognitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class RevenueRecognitionController extends ApiController
{
    public function __construct(
        protected LedgerService $ledger,
        protected RecognitionService $recognition,
        protected NotificationService $notifications,
    ) {}

    /**
     * List invoices eligible for (or already covered by) revenue recognition with KPIs.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $tenant = $this->tenant($request);
        $period = $this->resolvePeriod($request->string('month')->value());
        $view = $request->string('view')->value() === 'recognized' ? 'recognized' : 'pending';

        $query = $view === 'recognized'
            ? $tenant->invoices()->whereNotNull('revenue_recognized_at')->latest('revenue_recognized_at')->latest('id')
            : $tenant->invoices()->eligibleForRecognition($period)->orderBy('period_end')->orderBy('id');

        $invoices = $query->with('customer')->paginate($request->integer('per_page', 15));

        $pendingBase = $tenant->invoices()->eligibleForRecognition($period);

        return InvoiceResource::collection($invoices)->additional([
            'meta' => [
                'period' => $period->format('Y-m'),
                'view' => $view,
                'kpis' => [
                    'deferred_balance' => $this->ledger->accountFor($tenant, LedgerService::DEFERRED_REVENUE)->balance(),
                    'recognized_total' => $this->ledger->accountFor($tenant, LedgerService::SUBSCRIPTION_REVENUE)->balance(),
                    'pending_count' => (clone $pendingBase)->count(),
                    'pending_amount' => (float) (clone $pendingBase)->sum('amount'),
                    'recognized_count' => $tenant->invoices()->whereNotNull('revenue_recognized_at')->count(),
                ],
            ],
        ]);
    }

    /**
     * Recognize revenue for all eligible invoices in the selected month
     * (Dr Deferred Revenue / Cr Subscription Revenue).
     */
    public function recognize(Request $request): JsonResponse
    {
        $validated = $request->validate(['month' => ['nullable', 'date_format:Y-m']]);

        $tenant = $this->tenant($request);
        $period = $this->resolvePeriod($validated['month'] ?? null);

        $result = $this->recognition->recognizeForTenant($tenant, $period);

        if ($result['count'] > 0) {
            $this->notifications->revenueRecognized($tenant, $result['count'], $result['amount'], $period);
        }

        return response()->json([
            'message' => $result['count'] > 0
                ? "تم الاعتراف بإيراد {$result['count']} فاتورة بإجمالي ".number_format($result['amount'], 2)." لفترة {$period->format('Y/m')}."
                : 'لا توجد فواتير مؤهلة للاعتراف بالإيراد لهذه الفترة.',
            'count' => $result['count'],
            'amount' => $result['amount'],
            'period' => $period->format('Y-m'),
        ]);
    }

    /**
     * Resolve a target month into a Carbon at the start of that month.
     */
    protected function resolvePeriod(?string $month): Carbon
    {
        return $month && preg_match('/^\d{4}-\d{2}$/', $month)
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();
    }
}
