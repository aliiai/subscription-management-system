<?php

namespace App\Http\Controllers\Company;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\PaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tenant;
use App\Services\LedgerService;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected LedgerService $ledger,
        protected NotificationService $notifications,
    ) {}

    /**
     * Display the company's payments with KPIs and filters.
     */
    public function index(Request $request): View
    {
        $tenant = $this->tenant($request);

        $payments = $this->filteredPayments($tenant, $request)->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('company.payments._results', [
                'payments' => $payments,
                'filters' => $this->filters($request),
            ]);
        }

        $totalCollected = (float) $tenant->payments()->sum('amount');
        $count = $tenant->payments()->count();

        $openStatuses = [InvoiceStatus::Unpaid->value, InvoiceStatus::PartiallyPaid->value];

        return view('company.payments.index', [
            'payments' => $payments,
            'filters' => $this->filters($request),
            'openInvoices' => $tenant->invoices()->whereIn('status', $openStatuses)
                ->with('customer')->latest('issue_date')->get(),
            'kpis' => [
                'total_collected' => $totalCollected,
                'count' => $count,
                'average' => $count > 0 ? round($totalCollected / $count, 2) : 0.0,
                'collected_this_month' => (float) $tenant->payments()
                    ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('amount'),
            ],
        ]);
    }

    /**
     * Build the filtered/searched payments query for the current tenant.
     *
     * @return Builder<Payment>
     */
    protected function filteredPayments(Tenant $tenant, Request $request)
    {
        return $tenant->payments()
            ->with(['customer', 'invoice'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->value();
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('invoice', fn ($i) => $i->where('invoice_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('method'), function ($query) use ($request) {
                $query->where('method', $request->string('method')->value());
            })
            ->when($request->date('from'), function ($query) use ($request) {
                $query->whereDate('paid_at', '>=', $request->date('from'));
            })
            ->when($request->date('to'), function ($query) use ($request) {
                $query->whereDate('paid_at', '<=', $request->date('to'));
            })
            ->latest('paid_at')
            ->latest('id');
    }

    /**
     * Normalize the active filters for the views.
     *
     * @return array{q: string, method: string, from: string, to: string}
     */
    protected function filters(Request $request): array
    {
        return [
            'q' => $request->string('q')->value(),
            'method' => $request->string('method')->value(),
            'from' => $request->string('from')->value(),
            'to' => $request->string('to')->value(),
        ];
    }

    /**
     * Record a payment against an invoice and post its accounting entry.
     */
    public function store(PaymentRequest $request): RedirectResponse
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

        return $this->redirectAfterStore($request)->with('status', 'تم تسجيل الدفعة بنجاح.');
    }

    /**
     * Redirect back to the page the payment was recorded from, defaulting to the payments list.
     */
    protected function redirectAfterStore(Request $request): RedirectResponse
    {
        $target = $request->string('redirect_to')->value();

        if ($target !== '' && str_starts_with($target, url('/'))) {
            return redirect()->to($target);
        }

        return redirect()->route('company.payments');
    }

    /**
     * Delete a payment, reversing its accounting entry and recalculating the invoice.
     */
    public function destroy(Request $request, Payment $payment): RedirectResponse
    {
        $this->authorizePayment($request, $payment);

        DB::transaction(function () use ($payment) {
            $invoice = $payment->invoice;

            $this->ledger->reverse($payment, "عكس دفعة على الفاتورة {$invoice->invoice_number}");

            $invoice->amount_paid = max(0, round((float) $invoice->amount_paid - (float) $payment->amount, 2));
            $invoice->status = $this->statusFor($invoice);
            $invoice->save();

            $payment->delete();
        });

        return redirect()->back()->with('status', 'تم حذف الدفعة وعكس قيدها.');
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

    /**
     * Ensure the payment belongs to the authenticated user's tenant.
     */
    protected function authorizePayment(Request $request, Payment $payment): void
    {
        abort_unless($payment->tenant_id === $request->user()->tenant_id, 403);
    }

    /**
     * Resolve the authenticated user's tenant, ensuring one exists.
     */
    protected function tenant(Request $request): Tenant
    {
        return $request->user()->tenant ?? abort(403);
    }
}
