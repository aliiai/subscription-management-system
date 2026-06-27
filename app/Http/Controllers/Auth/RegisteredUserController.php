<?php

namespace App\Http\Controllers\Auth;

use App\Enums\TenantStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * Provisions a new tenant (company) together with its first user, who
     * becomes the tenant owner.
     */
    public function store(RegisterRequest $request, LedgerService $ledger): RedirectResponse
    {
        $user = DB::transaction(function () use ($request, $ledger): User {
            $tenant = Tenant::create([
                'name' => $request->string('company_name')->value(),
                'email' => $request->input('company_email'),
                'phone' => $request->input('company_phone'),
                'status' => TenantStatus::Active,
            ]);

            $ledger->seedChartOfAccounts($tenant);

            return $tenant->users()->create([
                'name' => $request->string('name')->value(),
                'email' => $request->string('email')->value(),
                'password' => $request->string('password')->value(),
                'role' => UserRole::Company,
                'is_owner' => true,
            ]);
        });

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('company.dashboard');
    }
}
