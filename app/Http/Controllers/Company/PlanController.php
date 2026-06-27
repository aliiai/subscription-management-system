<?php

namespace App\Http\Controllers\Company;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\PlanRequest;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    /**
     * Display the company's subscription plans (returns only the results partial on AJAX requests).
     */
    public function index(Request $request): View
    {
        $plans = $this->filteredPlans($request)->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('company.plans._results', [
                'plans' => $plans,
                'filters' => $this->filters($request),
            ]);
        }

        return view('company.plans.index', [
            'plans' => $plans,
            'filters' => $this->filters($request),
        ]);
    }

    /**
     * Build the filtered/searched plans query for the current tenant.
     *
     * @return Builder<Plan>
     */
    protected function filteredPlans(Request $request)
    {
        $query = $this->tenant($request)->plans()
            ->withCount(['subscriptions as active_subscribers_count' => function ($query) {
                $query->where('status', SubscriptionStatus::Active);
            }])
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where('name', 'like', '%'.$request->string('q')->value().'%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->string('status')->value() === 'active');
            })
            ->when($request->filled('cycle'), function ($query) use ($request) {
                $query->where('billing_cycle', $request->string('cycle')->value());
            });

        return match ($request->string('sort')->value()) {
            'oldest' => $query->oldest(),
            'most_subscribers' => $query->orderByDesc('active_subscribers_count')->latest(),
            'least_subscribers' => $query->orderBy('active_subscribers_count')->latest(),
            default => $query->latest(),
        };
    }

    /**
     * Normalize the active filters for the views.
     *
     * @return array{q: string, status: string, cycle: string, sort: string}
     */
    protected function filters(Request $request): array
    {
        return [
            'q' => $request->string('q')->value(),
            'status' => $request->string('status')->value(),
            'cycle' => $request->string('cycle')->value(),
            'sort' => $request->string('sort')->value(),
        ];
    }

    /**
     * Store a newly created plan for the company.
     */
    public function store(PlanRequest $request): RedirectResponse
    {
        $this->tenant($request)->plans()->create($request->planAttributes());

        return redirect()->route('company.plans')->with('status', 'تم إنشاء الخطة بنجاح.');
    }

    /**
     * Update the given plan.
     */
    public function update(PlanRequest $request, Plan $plan): RedirectResponse
    {
        $this->authorizePlan($request, $plan);

        $plan->update($request->planAttributes());

        return redirect()->route('company.plans')->with('status', 'تم تحديث الخطة بنجاح.');
    }

    /**
     * Remove the given plan.
     */
    public function destroy(Request $request, Plan $plan): RedirectResponse
    {
        $this->authorizePlan($request, $plan);

        $plan->delete();

        return redirect()->route('company.plans')->with('status', 'تم حذف الخطة بنجاح.');
    }

    /**
     * Ensure the plan belongs to the authenticated user's tenant.
     */
    protected function authorizePlan(Request $request, Plan $plan): void
    {
        abort_unless($plan->tenant_id === $request->user()->tenant_id, 403);
    }

    /**
     * Resolve the authenticated user's tenant, ensuring one exists.
     */
    protected function tenant(Request $request): Tenant
    {
        return $request->user()->tenant ?? abort(403);
    }
}
