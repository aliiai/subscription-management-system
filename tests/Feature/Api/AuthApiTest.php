<?php

namespace Tests\Feature\Api;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_provisions_tenant_owner_and_chart_of_accounts(): void
    {
        $response = $this->postJson('/api/v1/register', [
            'company_name' => 'شركة الاختبار',
            'name' => 'المدير',
            'email' => 'owner@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'tenant' => ['id', 'name']]]);

        $user = User::firstWhere('email', 'owner@example.com');
        $this->assertNotNull($user);
        $this->assertTrue($user->isOwner());
        $this->assertSame('company', $user->role->value);
        $this->assertSame(4, $user->tenant->accounts()->count());
    }

    public function test_register_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/v1/register', [
            'company_name' => 'شركة',
            'name' => 'مدير',
            'email' => 'taken@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_login_returns_a_token_with_valid_credentials(): void
    {
        $user = User::factory()->create(['email' => 'login@example.com']);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'email']]);
        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'login@example.com']);

        $this->postJson('/api/v1/login', [
            'email' => 'login@example.com',
            'password' => 'wrong-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_me_returns_the_authenticated_user_and_company(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'شركتي']);
        $user = User::factory()->for($tenant)->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.tenant.name', 'شركتي');
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create(['email' => 'login@example.com']);
        $token = $this->postJson('/api/v1/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ])->json('token');

        $this->withToken($token)->postJson('/api/v1/logout')->assertOk();

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_protected_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/plans')->assertUnauthorized();
    }
}
