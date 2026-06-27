<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_portal_is_displayed(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_root_shows_landing_page_to_guests(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_new_companies_can_register(): void
    {
        $response = $this->post(route('register'), [
            'company_name' => 'Acme LLC',
            'company_email' => 'info@acme.test',
            'company_phone' => '+9647700000000',
            'name' => 'Owner Name',
            'email' => 'owner@acme.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('company.dashboard'));

        $this->assertDatabaseHas('tenants', [
            'name' => 'Acme LLC',
            'email' => 'info@acme.test',
            'status' => 'active',
        ]);

        $user = User::firstWhere('email', 'owner@acme.test');
        $this->assertNotNull($user);
        $this->assertSame(UserRole::Company, $user->role);
        $this->assertTrue($user->isOwner());
        $this->assertSame('Acme LLC', $user->tenant->name);
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $this->post(route('register'), [
            'company_name' => 'Mismatch LLC',
            'name' => 'Owner',
            'email' => 'mismatch@acme.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Different123!',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'mismatch@acme.test']);
        $this->assertDatabaseMissing('tenants', ['name' => 'Mismatch LLC']);
    }

    public function test_registration_always_forces_the_company_role(): void
    {
        $this->post(route('register'), [
            'company_name' => 'Sneaky LLC',
            'name' => 'Sneaky User',
            'email' => 'sneaky@acme.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'admin',
        ]);

        $this->assertSame(UserRole::Company, User::firstWhere('email', 'sneaky@acme.test')->role);
    }

    public function test_company_users_can_authenticate(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('company.dashboard'));
    }

    public function test_users_cannot_authenticate_with_an_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
