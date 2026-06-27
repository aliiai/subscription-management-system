<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TenantStatus;
use App\Http\Requests\Api\V1\PasswordUpdateRequest;
use App\Http\Requests\Company\CompanyProfileRequest;
use App\Http\Requests\Company\ProfileUpdateRequest;
use App\Http\Resources\Api\V1\TenantResource;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingsController extends ApiController
{
    /**
     * Return the company and authenticated user profile.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'company' => new TenantResource($this->tenant($request)),
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * Update the company profile (name, contact info, logo).
     */
    public function updateCompany(CompanyProfileRequest $request): TenantResource
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

        return TenantResource::make($tenant->refresh());
    }

    /**
     * Update the authenticated user's personal profile.
     */
    public function updateProfile(ProfileUpdateRequest $request): UserResource
    {
        $request->user()->update($request->safe()->only(['name', 'email']));

        return UserResource::make($request->user()->refresh());
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(PasswordUpdateRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->string('password')->value()),
        ]);

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح.']);
    }

    /**
     * Deactivate (suspend) the company account and revoke all tokens.
     */
    public function deactivate(Request $request): JsonResponse
    {
        $tenant = $this->tenant($request);

        $tenant->update(['status' => TenantStatus::Suspended]);

        $tenant->users()->each(fn ($user) => $user->tokens()->delete());

        return response()->json(['message' => 'تم تعطيل حساب الشركة. تواصل مع الدعم لإعادة تفعيله.']);
    }
}
