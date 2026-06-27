<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantNotification;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * The selectable activity types with their Arabic labels.
     *
     * @var array<string, string>
     */
    protected array $types = [
        'customer' => 'العملاء',
        'subscription' => 'الاشتراكات',
        'invoice' => 'الفواتير',
        'payment' => 'المدفوعات',
        'revenue' => 'الإيرادات',
    ];

    /**
     * Display the tenant's activity log (returns only the results partial on AJAX requests).
     */
    public function index(Request $request): View
    {
        $tenant = $this->tenant($request);

        $activities = $this->filteredActivities($tenant, $request)->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return view('company.activity-log._results', [
                'activities' => $activities,
                'filters' => $this->filters($request),
            ]);
        }

        $base = $tenant->notifications();

        return view('company.activity-log.index', [
            'activities' => $activities,
            'filters' => $this->filters($request),
            'types' => $this->types,
            'stats' => [
                'total' => (clone $base)->count(),
                'today' => (clone $base)->whereDate('created_at', today())->count(),
                'week' => (clone $base)->where('created_at', '>=', now()->subDays(7))->count(),
                'month' => (clone $base)->where('created_at', '>=', now()->subDays(30))->count(),
            ],
        ]);
    }

    /**
     * Build the filtered/searched activity query for the current tenant.
     *
     * @return Builder<TenantNotification>
     */
    protected function filteredActivities(Tenant $tenant, Request $request): Builder
    {
        return $tenant->notifications()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->value();
                $query->where(function ($outer) use ($search) {
                    $outer->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('type') && array_key_exists($request->string('type')->value(), $this->types), function ($query) use ($request) {
                $query->where('type', $request->string('type')->value());
            })
            ->when($request->string('range')->value() === 'today', fn ($query) => $query->whereDate('created_at', today()))
            ->when($request->string('range')->value() === 'week', fn ($query) => $query->where('created_at', '>=', now()->subDays(7)))
            ->when($request->string('range')->value() === 'month', fn ($query) => $query->where('created_at', '>=', now()->subDays(30)))
            ->latest();
    }

    /**
     * Normalize the active filters for the views.
     *
     * @return array{q: string, type: string, range: string}
     */
    protected function filters(Request $request): array
    {
        return [
            'q' => $request->string('q')->value(),
            'type' => $request->string('type')->value(),
            'range' => $request->string('range')->value(),
        ];
    }

    /**
     * Resolve the authenticated user's tenant, ensuring one exists.
     */
    protected function tenant(Request $request): Tenant
    {
        return $request->user()->tenant ?? abort(403);
    }
}
