<?php

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_sees_only_its_tenant_subscriptions(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();

        $own = Subscription::factory()->for($tenant)->create([
            'customer_id' => Customer::factory()->for($tenant)->create(['name' => 'عميلي'])->id,
            'plan_id' => Plan::factory()->for($tenant)->create()->id,
        ]);
        Subscription::factory()->create(); // another tenant

        $this->actingAs($user)
            ->get(route('company.subscriptions'))
            ->assertOk()
            ->assertSee('عميلي');
    }

    public function test_company_can_create_a_subscription_and_sync_customer_plan(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $customer = Customer::factory()->for($tenant)->create();
        $plan = Plan::factory()->for($tenant)->create(['price' => 250]);

        $this->actingAs($user)->post(route('company.subscriptions.store'), [
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'start_date' => '2026-01-01',
            'status' => 'active',
        ])->assertRedirect(route('company.subscriptions'));

        $subscription = $tenant->subscriptions()->first();
        $this->assertNotNull($subscription);
        $this->assertSame('250.00', $subscription->price);
        // customers.plan_id is synced to the active subscription's plan.
        $this->assertSame($plan->id, $customer->refresh()->plan_id);
    }

    public function test_subscription_requires_customer_and_plan(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('company.subscriptions.store'), ['start_date' => '2026-01-01', 'status' => 'active'])
            ->assertSessionHasErrors(['customer_id', 'plan_id']);
    }

    public function test_cannot_use_customer_or_plan_from_another_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $foreignCustomer = Customer::factory()->for(Tenant::factory())->create();
        $foreignPlan = Plan::factory()->for(Tenant::factory())->create();

        $this->actingAs($user)->post(route('company.subscriptions.store'), [
            'customer_id' => $foreignCustomer->id,
            'plan_id' => $foreignPlan->id,
            'start_date' => '2026-01-01',
            'status' => 'active',
        ])->assertSessionHasErrors(['customer_id', 'plan_id']);
    }

    public function test_canceling_subscription_clears_customer_current_plan(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $customer = Customer::factory()->for($tenant)->create();
        $plan = Plan::factory()->for($tenant)->create();
        $subscription = Subscription::factory()->for($tenant)->create([
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
        ]);
        $customer->update(['plan_id' => $plan->id]);

        $this->actingAs($user)->put(route('company.subscriptions.update', $subscription), [
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'start_date' => '2026-01-01',
            'status' => 'canceled',
        ])->assertRedirect(route('company.subscriptions'));

        $this->assertSame(SubscriptionStatus::Canceled, $subscription->refresh()->status);
        $this->assertNull($customer->refresh()->plan_id);
    }

    public function test_company_cannot_update_another_tenant_subscription(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $foreign = Subscription::factory()->create();

        $this->actingAs($user)->put(route('company.subscriptions.update', $foreign), [
            'customer_id' => Customer::factory()->for($tenant)->create()->id,
            'plan_id' => Plan::factory()->for($tenant)->create()->id,
            'start_date' => '2026-01-01',
            'status' => 'active',
        ])->assertForbidden();
    }

    public function test_company_can_delete_its_subscription(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $subscription = Subscription::factory()->for($tenant)->create([
            'customer_id' => Customer::factory()->for($tenant)->create()->id,
            'plan_id' => Plan::factory()->for($tenant)->create()->id,
        ]);

        $this->actingAs($user)
            ->delete(route('company.subscriptions.destroy', $subscription))
            ->assertRedirect(route('company.subscriptions'));

        $this->assertModelMissing($subscription);
    }

    public function test_company_cannot_delete_another_tenant_subscription(): void
    {
        $user = User::factory()->create();
        $foreign = Subscription::factory()->create();

        $this->actingAs($user)
            ->delete(route('company.subscriptions.destroy', $foreign))
            ->assertForbidden();

        $this->assertModelExists($foreign);
    }
}
