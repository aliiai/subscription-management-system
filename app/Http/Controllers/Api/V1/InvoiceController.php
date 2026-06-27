<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\InvoiceStatus;
use App\Http\Requests\Company\InvoiceRequest;
use App\Http\Resources\Api\V1\InvoiceResource;
use App\Models\Invoice;
use App\Services\BillingService;
use App\Services\LedgerService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceController extends ApiController
{
    public function __construct(
        protected LedgerService $ledger,
        protected BillingService $billing,
        protected NotificationService $notifications,
    ) {}

    /**
     * List the company's invoices with optional filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $invoices = $this->tenant($request)->invoices()
            ->with(['customer', 'subscription.plan'])
            ->withCount('payments')
            ->when(in_array($request->string('status')->value(), InvoiceStatus::values(), true),
                fn ($query) => $query->where('status', $request->string('status')->value()))
            ->when($request->filled('customer'), fn ($query) => $query->where('customer_id', $request->integer('customer')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->value();
                $query->where(function ($inner) use ($search) {
                    $inner->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->date('from'), fn ($query) => $query->whereDate('issue_date', '>=', $request->date('from')))
            ->when($request->date('to'), fn ($query) => $query->whereDate('issue_date', '<=', $request->date('to')))
            ->latest('issue_date')
            ->latest('id')
            ->paginate($request->integer('per_page', 15));

        return InvoiceResource::collection($invoices);
    }

    /**
     * Create a manual invoice and post its issuing journal entry
     * (Dr Accounts Receivable / Cr Deferred Revenue).
     */
    public function store(InvoiceRequest $request): JsonResponse
    {
        $tenant = $this->tenant($request);

        $invoice = DB::transaction(function () use ($request, $tenant) {
            $subscription = null;
            if ($request->filled('subscription_id')) {
                $subscription = $tenant->subscriptions()->with('plan')->find($request->integer('subscription_id'));
                if ($subscription && $subscription->customer_id !== $request->integer('customer_id')) {
                    $subscription = null;
                }
            }

            $issueDate = $request->date('issue_date');

            $invoice = $tenant->invoices()->create([
                'customer_id' => $request->integer('customer_id'),
                'subscription_id' => $subscription?->id,
                'invoice_number' => Invoice::nextNumberFor($tenant),
                'issue_date' => $issueDate,
                'due_date' => $request->date('due_date'),
                'period_start' => $issueDate->copy()->startOfMonth(),
                'period_end' => $issueDate->copy()->endOfMonth(),
                'amount' => $request->float('amount'),
                'amount_paid' => 0,
                'currency' => $subscription?->plan?->currency ?? 'SAR',
                'status' => InvoiceStatus::Unpaid,
            ]);

            $this->ledger->recordInvoiceIssued($invoice);

            return $invoice;
        });

        $this->notifications->invoiceCreated($invoice->load('customer'));

        return InvoiceResource::make($invoice)->response()->setStatusCode(201);
    }

    /**
     * Generate monthly invoices for all active subscriptions (Cron simulation).
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate(['month' => ['nullable', 'date_format:Y-m']]);

        $tenant = $this->tenant($request);

        $period = isset($validated['month'])
            ? Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth()
            : now()->startOfMonth();

        $created = $this->billing->generateForTenant($tenant, $period);

        if ($created > 0) {
            $this->notifications->invoicesGenerated($tenant, $created, $period);
        }

        return response()->json([
            'message' => $created > 0
                ? "تم إنشاء {$created} فاتورة لفترة {$period->format('Y/m')}."
                : 'لا توجد اشتراكات بحاجة إلى فوترة لهذه الفترة.',
            'created' => $created,
            'period' => $period->format('Y-m'),
        ]);
    }

    /**
     * Show an invoice with its payments and journal entries.
     */
    public function show(Request $request, int $invoice): InvoiceResource
    {
        $model = $this->tenant($request)->invoices()->findOrFail($invoice);

        $model->load([
            'customer',
            'subscription.plan',
            'payments' => fn ($q) => $q->latest('paid_at'),
            'journalEntries' => fn ($q) => $q->with('lines.account')->latest('id'),
        ]);

        return InvoiceResource::make($model);
    }

    /**
     * Void an invoice that has no payments, reversing its accounting entry.
     */
    public function destroy(Request $request, int $invoice): JsonResponse|InvoiceResource
    {
        $model = $this->tenant($request)->invoices()->findOrFail($invoice);

        if ($model->status === InvoiceStatus::Void) {
            return response()->json(['message' => 'الفاتورة ملغاة بالفعل.'], 200);
        }

        if ($model->payments()->exists()) {
            return response()->json([
                'message' => 'لا يمكن إلغاء فاتورة عليها دفعات. احذف الدفعات أولاً.',
            ], 422);
        }

        DB::transaction(function () use ($model) {
            $this->ledger->reverse($model, "إلغاء الفاتورة {$model->invoice_number}");
            $model->update(['status' => InvoiceStatus::Void]);
        });

        $this->notifications->invoiceVoided($model);

        return InvoiceResource::make($model->refresh());
    }
}
