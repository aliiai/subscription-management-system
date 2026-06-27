<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function __construct(protected DashboardService $dashboard) {}

    /**
     * Return the company dashboard KPIs and statistics.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->dashboard->forTenant($this->tenant($request)),
        ]);
    }
}
