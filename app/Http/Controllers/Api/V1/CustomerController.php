<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SubscriptionStatus;
use App\Http\Requests\Company\CustomerRequest;
use App\Http\Resources\Api\V1\CustomerResource;
use App\Models\Customer;
use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class CustomerController extends ApiController
{
    public function __construct(protected NotificationService $notifications) {}

    /**
     * List the company's customers.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $customers = $this->tenant($request)->customers()
            ->with('plan')
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
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return CustomerResource::collection($customers);
    }

    /**
     * Create a new customer, opening a subscription when a plan is attached.
     */
    public function store(CustomerRequest $request): JsonResponse
    {
        $tenant = $this->tenant($request);

        $customer = DB::transaction(function () use ($request, $tenant) {
            $customer = $tenant->customers()->create($request->safe()->only(['name', 'email', 'phone', 'plan_id']));

            $this->syncSubscriptionWithCurrentPlan($tenant, $customer, $request);

            return $customer;
        });

        $this->notifications->customerCreated($customer);

        return CustomerResource::make($customer->load('plan'))->response()->setStatusCode(201);
    }

    /**
     * Show a single customer belonging to the company.
     */
    public function show(Request $request, int $customer): CustomerResource
    {
        return CustomerResource::make(
            $this->tenant($request)->customers()->with('plan')->findOrFail($customer)
        );
    }

    /**
     * Update the given customer, reconciling its subscription with the chosen plan.
     */
    public function update(CustomerRequest $request, int $customer): CustomerResource
    {
        $tenant = $this->tenant($request);
        $model = $tenant->customers()->findOrFail($customer);

        DB::transaction(function () use ($request, $tenant, $model) {
            $model->update($request->safe()->only(['name', 'email', 'phone', 'plan_id']));

            $this->syncSubscriptionWithCurrentPlan($tenant, $model, $request);
        });

        return CustomerResource::make($model->load('plan'));
    }

    /**
     * Delete the given customer.
     */
    public function destroy(Request $request, int $customer): JsonResponse
    {
        $this->tenant($request)->customers()->findOrFail($customer)->delete();

        return response()->json(null, 204);
    }

    /**
     * Keep the customer's active subscription in sync with the plan chosen on the request.
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
}
