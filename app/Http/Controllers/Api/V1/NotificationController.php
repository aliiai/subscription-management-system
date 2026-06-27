<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends ApiController
{
    /**
     * List the company's notifications.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $tenant = $this->tenant($request);

        $notifications = $tenant->notifications()
            ->when($request->boolean('unread'), fn ($query) => $query->unread())
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return NotificationResource::collection($notifications)->additional([
            'meta' => [
                'unread_count' => $tenant->notifications()->unread()->count(),
            ],
        ]);
    }

    /**
     * Open a notification (marking it read) and return it.
     */
    public function show(Request $request, int $notification): NotificationResource
    {
        $model = $this->tenant($request)->notifications()->findOrFail($notification);

        if ($model->isUnread()) {
            $model->update(['read_at' => now()]);
        }

        return NotificationResource::make($model);
    }

    /**
     * Mark all of the company's notifications as read.
     */
    public function readAll(Request $request): JsonResponse
    {
        $this->tenant($request)->notifications()->unread()->update(['read_at' => now()]);

        return response()->json(['message' => 'تم تعليم جميع الإشعارات كمقروءة.']);
    }
}
