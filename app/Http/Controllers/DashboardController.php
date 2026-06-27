<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboard) {}

    /**
     * Route an authenticated user to the dashboard matching their role.
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->role->dashboardRoute());
    }

    /**
     * Display the company dashboard with KPIs, charts and activity.
     */
    public function company(Request $request): View
    {
        $tenant = $request->user()->tenant ?? abort(403);

        return view('dashboard.company', [
            'data' => $this->dashboard->forTenant($tenant),
        ]);
    }
}
