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

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_sees_only_its_tenant_customers(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();

        Customer::factory()->for($tenant)->create(['name' => 'عميلي']);
        Customer::factory()->for(Tenant::factory())->create(['name' => 'عميل آخر']);

        $this->actingAs($user)
            ->get(route('company.customers'))
            ->assertOk()
            ->assertSee('عميلي')
            ->assertDontSee('عميل آخر');
    }

    public function test_company_can_create_a_customer_without_a_plan(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();

        $this->actingAs($user)->post(route('company.customers.store'), [
            'name' => 'شركة العميل',
            'email' => 'client@mail.test',
            'phone' => '0500000000',
        ])->assertRedirect(route('company.customers'));

        $customer = $tenant->customers()->firstWhere('name', 'شركة العميل');
        $this->assertNotNull($customer);
        $this->assertNull($customer->plan_id);
    }

    public function test_company_can_create_a_customer_with_its_own_plan(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $plan = Plan::factory()->for($tenant)->create();

        $this->actingAs($user)->post(route('company.customers.store'), [
            'name' => 'عميل بخطة',
            'plan_id' => $plan->id,
        ])->assertRedirect(route('company.customers'));

        $this->assertSame($plan->id, $tenant->customers()->firstWhere('name', 'عميل بخطة')->plan_id);
    }

    public function test_attaching_a_plan_on_create_creates_an_active_subscription(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $plan = Plan::factory()->for($tenant)->create(['price' => 150]);

        $this->actingAs($user)->post(route('company.customers.store'), [
            'name' => 'عميل مشترك',
            'plan_id' => $plan->id,
            'start_date' => '2026-02-01',
        ])->assertRedirect(route('company.customers'));

        $customer = $tenant->customers()->firstWhere('name', 'عميل مشترك');
        $subscription = $customer->subscriptions()->first();

        $this->assertNotNull($subscription);
        $this->assertSame(SubscriptionStatus::Active, $subscription->status);
        $this->assertSame('2026-02-01', $subscription->start_date->format('Y-m-d'));
        $this->assertSame('150.00', $subscription->price);

        $this->actingAs($user)
            ->get(route('company.subscriptions'))
            ->assertOk()
            ->assertSee('عميل مشترك');
    }

    public function test_attaching_a_plan_without_start_date_defaults_to_today(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $plan = Plan::factory()->for($tenant)->create();

        $this->actingAs($user)->post(route('company.customers.store'), [
            'name' => 'بلا تاريخ',
            'plan_id' => $plan->id,
        ])->assertRedirect();

        $subscription = $tenant->customers()->firstWhere('name', 'بلا تاريخ')->subscriptions()->first();

        $this->assertNotNull($subscription);
        $this->assertSame(now()->toDateString(), $subscription->start_date->format('Y-m-d'));
    }

    public function test_changing_customer_plan_cancels_old_subscription_and_creates_new(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $customer = Customer::factory()->for($tenant)->create();
        $oldPlan = Plan::factory()->for($tenant)->create();
        $newPlan = Plan::factory()->for($tenant)->create();

        Subscription::factory()->for($tenant)->create([
            'customer_id' => $customer->id,
            'plan_id' => $oldPlan->id,
            'status' => SubscriptionStatus::Active,
        ]);

        $this->actingAs($user)->put(route('company.customers.update', $customer), [
            'name' => $customer->name,
            'plan_id' => $newPlan->id,
            'start_date' => '2026-03-01',
        ])->assertRedirect();

        $this->assertSame($newPlan->id, $customer->refresh()->plan_id);

        $active = $customer->subscriptions()->where('status', SubscriptionStatus::Active)->get();
        $this->assertCount(1, $active);
        $this->assertSame($newPlan->id, $active->first()->plan_id);
        $this->assertSame(1, $customer->subscriptions()->where('status', SubscriptionStatus::Canceled)->count());
    }

    public function test_removing_plan_on_update_cancels_active_subscription(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $customer = Customer::factory()->for($tenant)->create();
        $plan = Plan::factory()->for($tenant)->create();

        Subscription::factory()->for($tenant)->create([
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
        ]);
        $customer->update(['plan_id' => $plan->id]);

        $this->actingAs($user)->put(route('company.customers.update', $customer), [
            'name' => $customer->name,
            'plan_id' => '',
        ])->assertRedirect();

        $this->assertNull($customer->refresh()->plan_id);
        $this->assertSame(0, $customer->subscriptions()->where('status', SubscriptionStatus::Active)->count());
    }

    public function test_customer_creation_requires_a_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('company.customers.store'), ['email' => 'x@mail.test'])
            ->assertSessionHasErrors('name');
    }

    public function test_cannot_attach_a_plan_from_another_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $foreignPlan = Plan::factory()->for(Tenant::factory())->create();

        $this->actingAs($user)->post(route('company.customers.store'), [
            'name' => 'عميل',
            'plan_id' => $foreignPlan->id,
        ])->assertSessionHasErrors('plan_id');
    }

    public function test_company_can_update_its_customer(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $customer = Customer::factory()->for($tenant)->create(['name' => 'قديم']);

        $this->actingAs($user)->put(route('company.customers.update', $customer), [
            'name' => 'محدّث',
        ])->assertRedirect(route('company.customers'));

        $this->assertSame('محدّث', $customer->refresh()->name);
    }

    public function test_company_cannot_update_another_tenant_customer(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->for(Tenant::factory())->create();

        $this->actingAs($user)->put(route('company.customers.update', $customer), [
            'name' => 'اختراق',
        ])->assertForbidden();
    }

    public function test_company_can_delete_its_customer(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $customer = Customer::factory()->for($tenant)->create();

        $this->actingAs($user)
            ->delete(route('company.customers.destroy', $customer))
            ->assertRedirect(route('company.customers'));

        $this->assertModelMissing($customer);
    }

    public function test_company_cannot_delete_another_tenant_customer(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->for(Tenant::factory())->create();

        $this->actingAs($user)
            ->delete(route('company.customers.destroy', $customer))
            ->assertForbidden();

        $this->assertModelExists($customer);
    }
}
