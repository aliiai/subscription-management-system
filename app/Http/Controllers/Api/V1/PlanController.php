<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SubscriptionStatus;
use App\Http\Requests\Company\PlanRequest;
use App\Http\Resources\Api\V1\PlanResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlanController extends ApiController
{
    /**
     * List the company's subscription plans.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $plans = $this->tenant($request)->plans()
            ->withCount(['subscriptions as active_subscribers_count' => function ($query) {
                $query->where('status', SubscriptionStatus::Active);
            }])
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q')->value().'%'))
            ->when($request->filled('cycle'), fn ($query) => $query->where('billing_cycle', $request->string('cycle')->value()))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->string('status')->value() === 'active'))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return PlanResource::collection($plans);
    }

    /**
     * Create a new plan for the company.
     */
    public function store(PlanRequest $request): JsonResponse
    {
        $plan = $this->tenant($request)->plans()->create($request->planAttributes());

        return PlanResource::make($plan)->response()->setStatusCode(201);
    }

    /**
     * Show a single plan belonging to the company.
     */
    public function show(Request $request, int $plan): PlanResource
    {
        return PlanResource::make($this->tenant($request)->plans()->findOrFail($plan));
    }

    /**
     * Update the given plan.
     */
    public function update(PlanRequest $request, int $plan): PlanResource
    {
        $model = $this->tenant($request)->plans()->findOrFail($plan);
        $model->update($request->planAttributes());

        return PlanResource::make($model);
    }

    /**
     * Delete the given plan.
     */
    public function destroy(Request $request, int $plan): JsonResponse
    {
        $this->tenant($request)->plans()->findOrFail($plan)->delete();

        return response()->json(null, 204);
    }
}
