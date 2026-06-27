<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display the full notifications list for the tenant.
     */
    public function index(Request $request): View
    {
        $tenant = $this->tenant($request);

        $notifications = $tenant->notifications()->latest()->paginate(20);

        return view('company.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $tenant->notifications()->unread()->count(),
        ]);
    }

    /**
     * Mark a single notification as read and redirect to its target.
     */
    public function open(Request $request, TenantNotification $notification): RedirectResponse
    {
        $this->authorizeNotification($request, $notification);

        if ($notification->isUnread()) {
            $notification->update(['read_at' => now()]);
        }

        return redirect()->to($notification->url ?? route('company.notifications'));
    }

    /**
     * Mark all of the tenant's notifications as read.
     */
    public function markAllRead(Request $request): RedirectResponse
    {
        $this->tenant($request)->notifications()->unread()->update(['read_at' => now()]);

        return redirect()->back()->with('status', 'تم تعليم جميع الإشعارات كمقروءة.');
    }

    /**
     * Ensure the notification belongs to the authenticated user's tenant.
     */
    protected function authorizeNotification(Request $request, TenantNotification $notification): void
    {
        abort_unless($notification->tenant_id === $request->user()->tenant_id, 403);
    }

    /**
     * Resolve the authenticated user's tenant, ensuring one exists.
     */
    protected function tenant(Request $request): Tenant
    {
        return $request->user()->tenant ?? abort(403);
    }
}
