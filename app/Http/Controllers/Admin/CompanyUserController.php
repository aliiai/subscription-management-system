<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyUserController extends Controller
{
    /**
     * Display all company (tenant) users across the platform.
     */
    public function index(): View
    {
        $users = User::query()
            ->whereNotNull('tenant_id')
            ->with('tenant')
            ->latest()
            ->get();

        return view('admin.company-users.index', ['users' => $users]);
    }

    /**
     * Update the given company user.
     */
    public function update(CompanyUserRequest $request, User $user): RedirectResponse
    {
        $this->ensureCompanyUser($user);

        $user->name = $request->string('name')->value();
        $user->email = $request->string('email')->value();
        $user->is_owner = $request->boolean('is_owner');

        if ($request->filled('password')) {
            $user->password = $request->string('password')->value();
        }

        $user->save();

        return redirect()->route('admin.company-users')->with('status', 'تم تحديث بيانات المستخدم بنجاح.');
    }

    /**
     * Remove the given company user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureCompanyUser($user);

        $user->delete();

        return redirect()->route('admin.company-users')->with('status', 'تم حذف المستخدم بنجاح.');
    }

    /**
     * Ensure the target user is a company (tenant) user, not a platform admin.
     */
    protected function ensureCompanyUser(User $user): void
    {
        abort_unless($user->tenant_id !== null && $user->isCompany(), 404);
    }
}
