<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_can_view_only_its_tenant_plans(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();

        Plan::factory()->for($tenant)->create(['name' => 'خطتي']);
        Plan::factory()->for(Tenant::factory())->create(['name' => 'خطة الآخرين']);

        $this->actingAs($user)
            ->get(route('company.plans'))
            ->assertOk()
            ->assertSee('خطتي')
            ->assertDontSee('خطة الآخرين');
    }

    public function test_company_can_create_a_plan(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();

        $response = $this->actingAs($user)->post(route('company.plans.store'), [
            'name' => 'الباقة الذهبية',
            'description' => 'وصف',
            'price' => 149.5,
            'currency' => 'SAR',
            'billing_cycle' => 'monthly',
            'features' => "ميزة أولى\nميزة ثانية",
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('company.plans'));

        $plan = $tenant->plans()->firstWhere('name', 'الباقة الذهبية');
        $this->assertNotNull($plan);
        $this->assertSame(['ميزة أولى', 'ميزة ثانية'], $plan->features);
        $this->assertTrue($plan->is_active);
    }

    public function test_plan_creation_requires_a_name(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();

        $this->actingAs($user)
            ->post(route('company.plans.store'), ['price' => 10, 'currency' => 'SAR', 'billing_cycle' => 'monthly'])
            ->assertSessionHasErrors('name');

        $this->assertSame(0, $tenant->plans()->count());
    }

    public function test_company_can_update_its_plan(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $plan = Plan::factory()->for($tenant)->create(['name' => 'قديم']);

        $this->actingAs($user)->put(route('company.plans.update', $plan), [
            'name' => 'محدّث',
            'price' => 200,
            'currency' => 'SAR',
            'billing_cycle' => 'yearly',
        ])->assertRedirect(route('company.plans'));

        $this->assertSame('محدّث', $plan->refresh()->name);
        $this->assertSame('yearly', $plan->billing_cycle->value);
    }

    public function test_company_cannot_update_another_tenant_plan(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->for(Tenant::factory())->create();

        $this->actingAs($user)->put(route('company.plans.update', $plan), [
            'name' => 'اختراق',
            'price' => 1,
            'currency' => 'SAR',
            'billing_cycle' => 'monthly',
        ])->assertForbidden();
    }

    public function test_company_can_delete_its_plan(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $plan = Plan::factory()->for($tenant)->create();

        $this->actingAs($user)
            ->delete(route('company.plans.destroy', $plan))
            ->assertRedirect(route('company.plans'));

        $this->assertModelMissing($plan);
    }

    public function test_company_cannot_delete_another_tenant_plan(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->for(Tenant::factory())->create();

        $this->actingAs($user)
            ->delete(route('company.plans.destroy', $plan))
            ->assertForbidden();

        $this->assertModelExists($plan);
    }
}
