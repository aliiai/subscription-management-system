<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_all_customers(): void
    {
        $admin = User::factory()->admin()->create();
        Customer::factory()->for(Tenant::factory())->create(['name' => 'عميل أ']);
        Customer::factory()->for(Tenant::factory())->create(['name' => 'عميل ب']);

        $this->actingAs($admin)
            ->get(route('admin.customers'))
            ->assertOk()
            ->assertSee('عميل أ')
            ->assertSee('عميل ب');
    }

    public function test_company_user_cannot_access_admin_customers(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.customers'))->assertForbidden();
    }
}
