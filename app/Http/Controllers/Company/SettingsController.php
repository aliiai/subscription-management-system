<?php

namespace App\Http\Controllers\Company;

use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\CompanyProfileRequest;
use App\Http\Requests\Company\PasswordUpdateRequest;
use App\Http\Requests\Company\ProfileUpdateRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the company settings page.
     */
    public function index(Request $request): View
    {
        return view('company.settings.index', [
            'tenant' => $this->tenant($request),
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the company profile (name, contact info, logo).
     */
    public function updateCompany(CompanyProfileRequest $request): RedirectResponse
    {
        $tenant = $this->tenant($request);

        $data = $request->safe()->only(['name', 'email', 'phone']);

        if ($request->boolean('remove_logo') && $tenant->logo_path) {
            Storage::disk('public')->delete($tenant->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($tenant->logo_path) {
                Storage::disk('public')->delete($tenant->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $tenant->update($data);

        return redirect()->route('company.settings')->with('status', 'تم تحديث بيانات الشركة بنجاح.');
    }

    /**
     * Update the authenticated user's personal profile.
     */
    public function updateProfile(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->update($request->safe()->only(['name', 'email']));

        return redirect()->route('company.settings')->with('status', 'تم تحديث ملفك الشخصي بنجاح.');
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->string('password')->value()),
        ]);

        return redirect()->route('company.settings')->with('status', 'تم تغيير كلمة المرور بنجاح.');
    }

    /**
     * Deactivate (suspend) the company account and log the user out.
     */
    public function deactivate(Request $request): RedirectResponse
    {
        $tenant = $this->tenant($request);

        $tenant->update(['status' => TenantStatus::Suspended]);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'تم تعطيل حساب الشركة. تواصل مع الدعم لإعادة تفعيله.');
    }

    /**
     * Resolve the authenticated user's tenant, ensuring one exists.
     */
    protected function tenant(Request $request): Tenant
    {
        return $request->user()->tenant ?? abort(403);
    }
}
