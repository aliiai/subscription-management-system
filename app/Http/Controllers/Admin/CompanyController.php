<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompanyRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Display all registered companies (tenants).
     */
    public function index(): View
    {
        $companies = Tenant::query()
            ->withCount(['users', 'plans'])
            ->latest()
            ->get();

        return view('admin.companies.index', ['companies' => $companies]);
    }

    /**
     * Update the given company.
     */
    public function update(CompanyRequest $request, Tenant $company): RedirectResponse
    {
        $company->name = $request->string('name')->value();
        $company->email = $request->input('email');
        $company->phone = $request->input('phone');
        $company->status = $request->boolean('is_active') ? TenantStatus::Active : TenantStatus::Suspended;
        $company->save();

        return redirect()->route('admin.companies')->with('status', 'تم تحديث بيانات الشركة بنجاح.');
    }

    /**
     * Remove the given company along with its related data.
     */
    public function destroy(Tenant $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('admin.companies')->with('status', 'تم حذف الشركة وكل بياناتها بنجاح.');
    }
}
