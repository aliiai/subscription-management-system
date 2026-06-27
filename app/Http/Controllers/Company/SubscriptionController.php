<?php

namespace App\Http\Controllers\Company;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\SubscriptionRequest;
use App\Models\Customer;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    /**
     * Display the company's subscriptions (returns only the results partial on AJAX requests).
     */
    public function index(Request $request): View
    {
        $tenant = $this->tenant($request);

        $subscriptions = $this->filteredSubscriptions($tenant, $request)->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('company.subscriptions._results', [
                'subscriptions' => $subscriptions,
                'filters' => $this->filters($request),
            ]);
        }

        return view('company.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'customers' => $tenant->customers()->orderBy('name')->get(),
            'plans' => $tenant->plans()->orderBy('name')->get(),
            'filters' => $this->filters($request),
        ]);
    }

    /**
     * Build the filtered/searched subscriptions query for the current tenant.
     *
     * @return Builder<Subscription>
     */
    protected function filteredSubscriptions(Tenant $tenant, Request $request)
    {
        return $tenant->subscriptions()
            ->with(['customer', 'plan'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->value();
                $query->where(function ($outer) use ($search) {
                    $outer->whereHas('customer', function ($customer) use ($search) {
                        $customer->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhereHas('plan', function ($plan) use ($search) {
                        $plan->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status')->value());
            })
            ->when($request->filled('plan'), function ($query) use ($request) {
                $query->where('plan_id', $request->integer('plan'));
            })
            ->latest('start_date');
    }

    /**
     * Normalize the active filters for the views.
     *
     * @return array{q: string, status: string, plan: string}
     */
    protected function filters(Request $request): array
    {
        return [
            'q' => $request->string('q')->value(),
            'status' => $request->string('status')->value(),
            'plan' => $request->string('plan')->value(),
        ];
    }

    /**
     * Store a newly created subscription and sync the customer's current plan.
     */
    public function store(SubscriptionRequest $request): RedirectResponse
    {
        $tenant = $this->tenant($request);

        $subscription = DB::transaction(function () use ($request, $tenant) {
            $plan = $tenant->plans()->findOrFail($request->integer('plan_id'));

            $subscription = $tenant->subscriptions()->create([
                'customer_id' => $request->integer('customer_id'),
                'plan_id' => $plan->id,
                'start_date' => $request->date('start_date'),
                'status' => $request->enum('status', SubscriptionStatus::class),
                'price' => $plan->price,
            ]);

            $this->syncCustomerCurrentPlan($subscription->customer);

            return $subscription;
        });

        $this->notifications->subscriptionCreated($subscription->load(['customer', 'plan']));

        return redirect()->route('company.subscriptions')->with('status', 'تم إنشاء الاشتراك بنجاح.');
    }

    /**
     * Update the given subscription and re-sync the customer's current plan.
     */
    public function update(SubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $this->authorizeSubscription($request, $subscription);

        $tenant = $this->tenant($request);

        DB::transaction(function () use ($request, $tenant, $subscription) {
            $plan = $tenant->plans()->findOrFail($request->integer('plan_id'));

            $subscription->update([
                'customer_id' => $request->integer('customer_id'),
                'plan_id' => $plan->id,
                'start_date' => $request->date('start_date'),
                'status' => $request->enum('status', SubscriptionStatus::class),
                'price' => $plan->price,
            ]);

            $this->syncCustomerCurrentPlan($subscription->customer()->first());
        });

        return redirect()->route('company.subscriptions')->with('status', 'تم تحديث الاشتراك بنجاح.');
    }

    /**
     * Remove the given subscription and re-sync the customer's current plan.
     */
    public function destroy(Request $request, Subscription $subscription): RedirectResponse
    {
        $this->authorizeSubscription($request, $subscription);

        DB::transaction(function () use ($subscription) {
            $customer = $subscription->customer()->first();
            $subscription->delete();

            if ($customer !== null) {
                $this->syncCustomerCurrentPlan($customer);
            }
        });

        return redirect()->route('company.subscriptions')->with('status', 'تم حذف الاشتراك بنجاح.');
    }

    /**
     * Point the customer's current plan to their latest active subscription (or none).
     */
    protected function syncCustomerCurrentPlan(Customer $customer): void
    {
        $active = $customer->subscriptions()
            ->where('status', SubscriptionStatus::Active)
            ->latest('start_date')
            ->first();

        $customer->plan_id = $active?->plan_id;
        $customer->save();
    }

    /**
     * Ensure the subscription belongs to the authenticated user's tenant.
     */
    protected function authorizeSubscription(Request $request, Subscription $subscription): void
    {
        abort_unless($subscription->tenant_id === $request->user()->tenant_id, 403);
    }

    /**
     * Resolve the authenticated user's tenant, ensuring one exists.
     */
    protected function tenant(Request $request): Tenant
    {
        return $request->user()->tenant ?? abort(403);
    }
}
