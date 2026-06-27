<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SubscriptionStatus;
use App\Http\Requests\Company\SubscriptionRequest;
use App\Http\Resources\Api\V1\SubscriptionResource;
use App\Models\Customer;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends ApiController
{
    public function __construct(protected NotificationService $notifications) {}

    /**
     * List the company's subscriptions.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $subscriptions = $this->tenant($request)->subscriptions()
            ->with(['customer', 'plan'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->value()))
            ->when($request->filled('plan'), fn ($query) => $query->where('plan_id', $request->integer('plan')))
            ->when($request->filled('customer'), fn ($query) => $query->where('customer_id', $request->integer('customer')))
            ->latest('start_date')
            ->paginate($request->integer('per_page', 15));

        return SubscriptionResource::collection($subscriptions);
    }

    /**
     * Link a customer to a plan, creating a subscription.
     */
    public function store(SubscriptionRequest $request): JsonResponse
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

        return SubscriptionResource::make($subscription)->response()->setStatusCode(201);
    }

    /**
     * Show a single subscription belonging to the company.
     */
    public function show(Request $request, int $subscription): SubscriptionResource
    {
        return SubscriptionResource::make(
            $this->tenant($request)->subscriptions()->with(['customer', 'plan'])->findOrFail($subscription)
        );
    }

    /**
     * Update the given subscription and re-sync the customer's current plan.
     */
    public function update(SubscriptionRequest $request, int $subscription): SubscriptionResource
    {
        $tenant = $this->tenant($request);
        $model = $tenant->subscriptions()->findOrFail($subscription);

        DB::transaction(function () use ($request, $tenant, $model) {
            $plan = $tenant->plans()->findOrFail($request->integer('plan_id'));

            $model->update([
                'customer_id' => $request->integer('customer_id'),
                'plan_id' => $plan->id,
                'start_date' => $request->date('start_date'),
                'status' => $request->enum('status', SubscriptionStatus::class),
                'price' => $plan->price,
            ]);

            $this->syncCustomerCurrentPlan($model->customer()->first());
        });

        return SubscriptionResource::make($model->load(['customer', 'plan']));
    }

    /**
     * Cancel/delete the given subscription and re-sync the customer's current plan.
     */
    public function destroy(Request $request, int $subscription): JsonResponse
    {
        $model = $this->tenant($request)->subscriptions()->findOrFail($subscription);

        DB::transaction(function () use ($model) {
            $customer = $model->customer()->first();
            $model->delete();

            if ($customer !== null) {
                $this->syncCustomerCurrentPlan($customer);
            }
        });

        return response()->json(null, 204);
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
}
