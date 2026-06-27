<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    /**
     * Resolve the authenticated user's tenant, ensuring one exists.
     *
     * All data access in the API flows through this tenant relationship so a
     * company can never read or mutate another tenant's records.
     */
    protected function tenant(Request $request): Tenant
    {
        return $request->user()->tenant ?? abort(403, 'لا توجد شركة مرتبطة بهذا الحساب.');
    }
}
