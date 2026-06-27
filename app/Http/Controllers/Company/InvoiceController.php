<?php

namespace App\Http\Controllers\Company;

use App\Enums\InvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\InvoiceRequest;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Services\BillingService;
use App\Services\LedgerService;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected LedgerService $ledger,
        protected BillingService $billing,
        protected NotificationService $notifications,
    ) {}

    /**
     * Display the company's invoices with KPIs and filters.
     */
    public function index(Request $request): View
    {
        $tenant = $this->tenant($request);

        $invoices = $this->filteredInvoices($tenant, $request)->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('company.invoices._results', [
                'invoices' => $invoices,
                'filters' => $this->filters($request),
            ]);
        }

        $openStatuses = [InvoiceStatus::Unpaid->value, InvoiceStatus::PartiallyPaid->value];

        return view('company.invoices.index', [
            'invoices' => $invoices,
            'filters' => $this->filters($request),
            'customers' => $tenant->customers()->orderBy('name')->get(),
            'subscriptions' => $tenant->subscriptions()
                ->where('status', SubscriptionStatus::Active)
                ->with('plan')
                ->get(),
            'openInvoices' => $tenant->invoices()->whereIn('status', $openStatuses)
                ->with('customer')->latest('issue_date')->get(),
            'kpis' => [
                'outstanding' => (float) $tenant->invoices()->whereIn('status', $openStatuses)
                    ->selectRaw('COALESCE(SUM(amount - amount_paid), 0) as total')->value('total'),
                'collected_this_month' => (float) $tenant->payments()
                    ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('amount'),
                'unpaid_count' => $tenant->invoices()->whereIn('status', $openStatuses)->count(),
                'overdue_count' => $tenant->invoices()->whereIn('status', $openStatuses)
                    ->whereDate('due_date', '<', now())->count(),
            ],
        ]);
    }

    /**
     * Build the filtered/searched invoices query for the current tenant.
     *
     * @return Builder<Invoice>
     */
    protected function filteredInvoices(Tenant $tenant, Request $request)
    {
        return $tenant->invoices()
            ->with(['customer', 'subscription.plan'])
            ->withCount('payments')
            ->when(in_array($request->string('status')->value(), InvoiceStatus::values(), true), function ($query) use ($request) {
                $query->where('status', $request->string('status')->value());
            })
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->value();
                $query->where(function ($inner) use ($search) {
                    $inner->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->date('from'), function ($query) use ($request) {
                $query->whereDate('issue_date', '>=', $request->date('from'));
            })
            ->when($request->date('to'), function ($query) use ($request) {
                $query->whereDate('issue_date', '<=', $request->date('to'));
            })
            ->latest('issue_date')
            ->latest('id');
    }

    /**
     * Normalize the active filters for the views.
     *
     * @return array{status: string, q: string, from: string, to: string}
     */
    protected function filters(Request $request): array
    {
        return [
            'status' => $request->string('status')->value(),
            'q' => $request->string('q')->value(),
            'from' => $request->string('from')->value(),
            'to' => $request->string('to')->value(),
        ];
    }

    /**
     * Store a manually created invoice and post its accounting entry.
     */
    public function store(InvoiceRequest $request): RedirectResponse
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

        return redirect()->route('company.invoices')->with('status', 'تم إنشاء الفاتورة بنجاح.');
    }

    /**
     * Generate monthly invoices for all active subscriptions (Cron simulation).
     */
    public function generate(Request $request): RedirectResponse
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

        $message = $created > 0
            ? "تم إنشاء {$created} فاتورة لفترة {$period->format('Y/m')}."
            : 'لا توجد اشتراكات بحاجة إلى فوترة لهذه الفترة.';

        return redirect()->route('company.invoices')->with('status', $message);
    }

    /**
     * Show the invoice details with its payments and linked journal entries.
     */
    public function show(Request $request, Invoice $invoice): View
    {
        $this->authorizeInvoice($request, $invoice);

        $invoice->load([
            'customer',
            'subscription.plan',
            'payments' => fn ($q) => $q->latest('paid_at'),
            'journalEntries' => fn ($q) => $q->with('lines.account')->latest('id'),
        ]);

        return view('company.invoices.show', ['invoice' => $invoice]);
    }

    /**
     * Void an invoice that has no payments, reversing its accounting entry.
     */
    public function void(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($request, $invoice);

        if ($invoice->status === InvoiceStatus::Void) {
            return redirect()->route('company.invoices')->with('status', 'الفاتورة ملغاة بالفعل.');
        }

        if ($invoice->payments()->exists()) {
            return redirect()->back()->with('error', 'لا يمكن إلغاء فاتورة عليها دفعات. احذف الدفعات أولاً.');
        }

        DB::transaction(function () use ($invoice) {
            $this->ledger->reverse($invoice, "إلغاء الفاتورة {$invoice->invoice_number}");
            $invoice->update(['status' => InvoiceStatus::Void]);
        });

        $this->notifications->invoiceVoided($invoice);

        return redirect()->route('company.invoices')->with('status', 'تم إلغاء الفاتورة.');
    }

    /**
     * Ensure the invoice belongs to the authenticated user's tenant.
     */
    protected function authorizeInvoice(Request $request, Invoice $invoice): void
    {
        abort_unless($invoice->tenant_id === $request->user()->tenant_id, 403);
    }

    /**
     * Resolve the authenticated user's tenant, ensuring one exists.
     */
    protected function tenant(Request $request): Tenant
    {
        return $request->user()->tenant ?? abort(403);
    }
}
