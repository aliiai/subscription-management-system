<?php

namespace App\Http\Controllers\Company;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\CustomerRequest;
use App\Models\Customer;
use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    /**
     * Display the company's customers (returns only the results partial on AJAX requests).
     */
    public function index(Request $request): View
    {
        $tenant = $this->tenant($request);

        $customers = $this->filteredCustomers($tenant, $request)->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('company.customers._results', [
                'customers' => $customers,
                'filters' => $this->filters($request),
            ]);
        }

        return view('company.customers.index', [
            'customers' => $customers,
            'plans' => $tenant->plans()->orderBy('name')->get(),
            'filters' => $this->filters($request),
        ]);
    }

    /**
     * Build the filtered/searched customers query for the current tenant.
     *
     * @return Builder<Customer>
     */
    protected function filteredCustomers(Tenant $tenant, Request $request)
    {
        return $tenant->customers()
            ->with('plan')
            ->withMax(['subscriptions as current_start_date' => function ($query) {
                $query->where('status', SubscriptionStatus::Active);
            }], 'start_date')
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->value();
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('plan'), function ($query) use ($request) {
                $plan = $request->string('plan')->value();
                $plan === 'none'
                    ? $query->whereNull('plan_id')
                    : $query->where('plan_id', $request->integer('plan'));
            })
            ->latest();
    }

    /**
     * Normalize the active filters for the views.
     *
     * @return array{q: string, plan: string}
     */
    protected function filters(Request $request): array
    {
        return [
            'q' => $request->string('q')->value(),
            'plan' => $request->string('plan')->value(),
        ];
    }

    /**
     * Store a newly created customer, creating a subscription when a plan is attached.
     */
    public function store(CustomerRequest $request): RedirectResponse
    {
        $tenant = $this->tenant($request);

        $customer = DB::transaction(function () use ($request, $tenant) {
            $customer = $tenant->customers()->create($request->safe()->only(['name', 'email', 'phone', 'plan_id']));

            $this->syncSubscriptionWithCurrentPlan($tenant, $customer, $request);

            return $customer;
        });

        $this->notifications->customerCreated($customer);

        return redirect()->route('company.customers')->with('status', 'تم إضافة العميل بنجاح.');
    }

    /**
     * Update the given customer, reconciling its subscription with the chosen plan.
     */
    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorizeCustomer($request, $customer);

        $tenant = $this->tenant($request);

        DB::transaction(function () use ($request, $tenant, $customer) {
            $customer->update($request->safe()->only(['name', 'email', 'phone', 'plan_id']));

            $this->syncSubscriptionWithCurrentPlan($tenant, $customer, $request);
        });

        return redirect()->route('company.customers')->with('status', 'تم تحديث بيانات العميل بنجاح.');
    }

    /**
     * Keep the customer's active subscription in sync with the plan chosen on the form.
     */
    protected function syncSubscriptionWithCurrentPlan(Tenant $tenant, Customer $customer, CustomerRequest $request): void
    {
        $planId = $request->filled('plan_id') ? $request->integer('plan_id') : null;

        if ($planId === null) {
            $customer->subscriptions()
                ->where('status', SubscriptionStatus::Active)
                ->update(['status' => SubscriptionStatus::Canceled]);

            return;
        }

        $plan = $tenant->plans()->find($planId);

        if ($plan === null) {
            return;
        }

        $startDate = $request->filled('start_date') ? $request->date('start_date') : now();

        $existing = $customer->subscriptions()
            ->where('status', SubscriptionStatus::Active)
            ->where('plan_id', $plan->id)
            ->latest('start_date')
            ->first();

        // Cancel any other active subscriptions so the customer has a single current plan.
        $customer->subscriptions()
            ->where('status', SubscriptionStatus::Active)
            ->when($existing, fn ($query) => $query->whereKeyNot($existing->getKey()))
            ->where('plan_id', '!=', $plan->id)
            ->update(['status' => SubscriptionStatus::Canceled]);

        if ($existing !== null) {
            $existing->update(['start_date' => $startDate, 'price' => $plan->price]);

            return;
        }

        $tenant->subscriptions()->create([
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'start_date' => $startDate,
            'status' => SubscriptionStatus::Active,
            'price' => $plan->price,
        ]);
    }

    /**
     * Remove the given customer.
     */
    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        $this->authorizeCustomer($request, $customer);

        $customer->delete();

        return redirect()->route('company.customers')->with('status', 'تم حذف العميل بنجاح.');
    }

    /**
     * Ensure the customer belongs to the authenticated user's tenant.
     */
    protected function authorizeCustomer(Request $request, Customer $customer): void
    {
        abort_unless($customer->tenant_id === $request->user()->tenant_id, 403);
    }

    /**
     * Resolve the authenticated user's tenant, ensuring one exists.
     */
    protected function tenant(Request $request): Tenant
    {
        return $request->user()->tenant ?? abort(403);
    }
}
