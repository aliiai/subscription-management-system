<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Http\Requests\Company\PaymentRequest;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Invoice;
use App\Services\LedgerService;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class PaymentController extends ApiController
{
    public function __construct(
        protected LedgerService $ledger,
        protected NotificationService $notifications,
    ) {}

    /**
     * List the company's payments with optional filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $payments = $this->tenant($request)->payments()
            ->with(['customer', 'invoice'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->value();
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('invoice', fn ($i) => $i->where('invoice_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('method'), fn ($query) => $query->where('method', $request->string('method')->value()))
            ->when($request->filled('invoice'), fn ($query) => $query->where('invoice_id', $request->integer('invoice')))
            ->when($request->date('from'), fn ($query) => $query->whereDate('paid_at', '>=', $request->date('from')))
            ->when($request->date('to'), fn ($query) => $query->whereDate('paid_at', '<=', $request->date('to')))
            ->latest('paid_at')
            ->latest('id')
            ->paginate($request->integer('per_page', 15));

        return PaymentResource::collection($payments);
    }

    /**
     * Record a payment against an invoice and post its entry (Dr Cash / Cr Accounts Receivable).
     */
    public function store(PaymentRequest $request): JsonResponse
    {
        $tenant = $this->tenant($request);

        $payment = DB::transaction(function () use ($request, $tenant) {
            $invoice = $tenant->invoices()->findOrFail($request->integer('invoice_id'));

            $payment = $tenant->payments()->create([
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'amount' => $request->float('amount'),
                'paid_at' => $request->date('paid_at'),
                'method' => $request->enum('method', PaymentMethod::class),
                'reference' => $request->input('reference'),
            ]);

            $invoice->amount_paid = round((float) $invoice->amount_paid + $request->float('amount'), 2);
            $invoice->status = $this->statusFor($invoice);
            $invoice->save();

            $this->ledger->recordPaymentReceived($payment);

            return $payment;
        });

        $this->notifications->paymentReceived($payment->load(['customer', 'invoice']));

        return PaymentResource::make($payment)->response()->setStatusCode(201);
    }

    /**
     * Show a single payment belonging to the company.
     */
    public function show(Request $request, int $payment): PaymentResource
    {
        return PaymentResource::make(
            $this->tenant($request)->payments()->with(['customer', 'invoice'])->findOrFail($payment)
        );
    }

    /**
     * Delete a payment, reversing its accounting entry and recalculating the invoice.
     */
    public function destroy(Request $request, int $payment): JsonResponse
    {
        $model = $this->tenant($request)->payments()->findOrFail($payment);

        DB::transaction(function () use ($model) {
            $invoice = $model->invoice;

            $this->ledger->reverse($model, "عكس دفعة على الفاتورة {$invoice->invoice_number}");

            $invoice->amount_paid = max(0, round((float) $invoice->amount_paid - (float) $model->amount, 2));
            $invoice->status = $this->statusFor($invoice);
            $invoice->save();

            $model->delete();
        });

        return response()->json(null, 204);
    }

    /**
     * Determine an invoice status from its paid amount.
     */
    protected function statusFor(Invoice $invoice): InvoiceStatus
    {
        $paid = (float) $invoice->amount_paid;
        $total = (float) $invoice->amount;

        return match (true) {
            $paid >= $total => InvoiceStatus::Paid,
            $paid > 0 => InvoiceStatus::PartiallyPaid,
            default => InvoiceStatus::Unpaid,
        };
    }
}
