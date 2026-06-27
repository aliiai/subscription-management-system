<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityLogController extends ApiController
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
     * List the company's activity log (backed by tenant notifications).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $tenant = $this->tenant($request);

        $activities = $tenant->notifications()
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->string('q')->value();
                $query->where(function ($outer) use ($search) {
                    $outer->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('type') && array_key_exists($request->string('type')->value(), $this->types),
                fn ($query) => $query->where('type', $request->string('type')->value()))
            ->when($request->string('range')->value() === 'today', fn ($query) => $query->whereDate('created_at', today()))
            ->when($request->string('range')->value() === 'week', fn ($query) => $query->where('created_at', '>=', now()->subDays(7)))
            ->when($request->string('range')->value() === 'month', fn ($query) => $query->where('created_at', '>=', now()->subDays(30)))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return NotificationResource::collection($activities);
    }
}
