<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirects_company_to_company_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('company.dashboard'));
    }

    public function test_guests_are_redirected_to_login_from_protected_pages(): void
    {
        $this->get(route('company.dashboard'))->assertRedirect(route('login'));
    }

    public function test_company_can_view_its_section_pages(): void
    {
        $user = User::factory()->create();

        foreach (['company.customers', 'company.invoices', 'company.income-statement', 'company.settings'] as $route) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }
    }
}
