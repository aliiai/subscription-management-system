<?php

namespace Tests\Feature\Api;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlanApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_only_the_tenant_plans(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        Plan::factory()->for($tenant)->create(['name' => 'خطتي']);
        Plan::factory()->for(Tenant::factory())->create(['name' => 'خطة الآخرين']);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/plans')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'خطتي');
    }

    public function test_can_create_a_plan_with_array_features(): void
    {
        $tenant = Tenant::factory()->create();
        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $this->postJson('/api/v1/plans', [
            'name' => 'الباقة الذهبية',
            'price' => 149.5,
            'currency' => 'SAR',
            'billing_cycle' => 'monthly',
            'features' => ['ميزة أولى', 'ميزة ثانية'],
            'is_active' => true,
        ])->assertCreated()->assertJsonPath('data.name', 'الباقة الذهبية');

        $plan = $tenant->plans()->firstWhere('name', 'الباقة الذهبية');
        $this->assertSame(['ميزة أولى', 'ميزة ثانية'], $plan->features);
    }

    public function test_create_requires_a_name(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/plans', ['price' => 10, 'currency' => 'SAR', 'billing_cycle' => 'monthly'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_can_update_its_plan(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->for($tenant)->create(['name' => 'قديم']);
        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $this->putJson("/api/v1/plans/{$plan->id}", [
            'name' => 'محدّث',
            'price' => 200,
            'currency' => 'SAR',
            'billing_cycle' => 'yearly',
        ])->assertOk()->assertJsonPath('data.name', 'محدّث');

        $this->assertSame('yearly', $plan->refresh()->billing_cycle->value);
    }

    public function test_can_delete_its_plan(): void
    {
        $tenant = Tenant::factory()->create();
        $plan = Plan::factory()->for($tenant)->create();
        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $this->deleteJson("/api/v1/plans/{$plan->id}")->assertNoContent();
        $this->assertModelMissing($plan);
    }

    public function test_cannot_access_another_tenant_plan(): void
    {
        $plan = Plan::factory()->for(Tenant::factory())->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/v1/plans/{$plan->id}")->assertNotFound();
        $this->putJson("/api/v1/plans/{$plan->id}", [
            'name' => 'اختراق', 'price' => 1, 'currency' => 'SAR', 'billing_cycle' => 'monthly',
        ])->assertNotFound();
        $this->deleteJson("/api/v1/plans/{$plan->id}")->assertNotFound();

        $this->assertModelExists($plan);
    }
}
