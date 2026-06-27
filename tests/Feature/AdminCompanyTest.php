<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_companies(): void
    {
        $admin = User::factory()->admin()->create();
        Tenant::factory()->create(['name' => 'شركة الاختبار']);

        $this->actingAs($admin)
            ->get(route('admin.companies'))
            ->assertOk()
            ->assertSee('شركة الاختبار');
    }

    public function test_company_user_cannot_access_companies(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.companies'))->assertForbidden();
    }

    public function test_admin_can_update_a_company(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Tenant::factory()->create(['name' => 'قديم']);

        $this->actingAs($admin)->put(route('admin.companies.update', $company), [
            'name' => 'محدّث',
            'email' => 'new@company.test',
            'phone' => '0500000000',
        ])->assertRedirect(route('admin.companies'));

        $company->refresh();
        $this->assertSame('محدّث', $company->name);
        $this->assertSame('new@company.test', $company->email);
        // Toggle omitted => company becomes suspended.
        $this->assertFalse($company->isActive());
    }

    public function test_company_update_requires_a_name(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Tenant::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.companies.update', $company), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_admin_can_delete_a_company_with_related_data(): void
    {
        $admin = User::factory()->admin()->create();
        $company = Tenant::factory()->create();
        $user = User::factory()->for($company)->create();
        $plan = Plan::factory()->for($company)->create();

        $this->actingAs($admin)
            ->delete(route('admin.companies.destroy', $company))
            ->assertRedirect(route('admin.companies'));

        $this->assertModelMissing($company);
        $this->assertModelMissing($user);
        $this->assertModelMissing($plan);
    }
}
