<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TenantStatus;
use App\Enums\UserRole;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    /**
     * Register a new company (tenant) with its first owner user and issue a token.
     */
    public function register(RegisterRequest $request, LedgerService $ledger): JsonResponse
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

        $token = $user->createToken($this->deviceName($request))->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->load('tenant')),
        ], 201);
    }

    /**
     * Authenticate a user by credentials and issue a personal access token.
     *
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->string('email')->value())->first();

        if ($user === null || ! Hash::check($request->string('password')->value(), $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $token = $user->createToken($this->deviceName($request))->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->load('tenant')),
        ]);
    }

    /**
     * Return the currently authenticated user and their company.
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user()->load('tenant'));
    }

    /**
     * Revoke the token used for the current request.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'تم تسجيل الخروج.']);
    }

    /**
     * Resolve a human-friendly device name for the issued token.
     */
    protected function deviceName(Request $request): string
    {
        return $request->string('device_name')->value()
            ?: ($request->userAgent() ?? 'api-token');
    }
}
