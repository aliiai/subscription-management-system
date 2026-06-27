<?php

namespace Tests\Feature\Api;

use App\Enums\InvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BillingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function assertLedgerBalanced(Tenant $tenant): void
    {
        $debit = 0.0;
        $credit = 0.0;

        $tenant->journalEntries()->with('lines')->get()->each(function ($entry) use (&$debit, &$credit) {
            $debit += (float) $entry->lines->sum('debit');
            $credit += (float) $entry->lines->sum('credit');
        });

        $this->assertSame(round($debit, 2), round($credit, 2), 'Ledger is not balanced.');
    }

    protected function balanceOf(Tenant $tenant, string $code): float
    {
        return app(LedgerService::class)->accountFor($tenant, $code)->balance();
    }

    public function test_creating_invoice_posts_receivable_and_deferred_revenue(): void
    {
        $tenant = Tenant::factory()->create();
        $customer = Customer::factory()->for($tenant)->create();
        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $this->postJson('/api/v1/invoices', [
            'customer_id' => $customer->id,
            'issue_date' => '2026-01-01',
            'due_date' => '2026-01-15',
            'amount' => 100,
        ])->assertCreated()->assertJsonPath('data.status', 'unpaid');

        $this->assertEqualsWithDelta(100, $this->balanceOf($tenant, LedgerService::ACCOUNTS_RECEIVABLE), 0.001);
        $this->assertEqualsWithDelta(100, $this->balanceOf($tenant, LedgerService::DEFERRED_REVENUE), 0.001);
        $this->assertLedgerBalanced($tenant);
    }

    public function test_recording_payment_posts_cash_and_settles_invoice(): void
    {
        $tenant = Tenant::factory()->create();
        $customer = Customer::factory()->for($tenant)->create();
        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $invoice = Invoice::factory()->for($tenant)->create([
            'customer_id' => $customer->id,
            'amount' => 100,
            'amount_paid' => 0,
            'status' => InvoiceStatus::Unpaid,
        ]);
        app(LedgerService::class)->recordInvoiceIssued($invoice);

        $this->postJson('/api/v1/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 100,
            'paid_at' => '2026-01-10',
            'method' => 'cash',
        ])->assertCreated();

        $this->assertSame(InvoiceStatus::Paid, $invoice->refresh()->status);
        $this->assertEqualsWithDelta(100, $this->balanceOf($tenant, LedgerService::CASH), 0.001);
        $this->assertEqualsWithDelta(0, $this->balanceOf($tenant, LedgerService::ACCOUNTS_RECEIVABLE), 0.001);
        $this->assertLedgerBalanced($tenant);
    }

    public function test_payment_cannot_exceed_invoice_balance(): void
    {
        $tenant = Tenant::factory()->create();
        $invoice = Invoice::factory()->for($tenant)->create([
            'customer_id' => Customer::factory()->for($tenant)->create()->id,
            'amount' => 100,
            'amount_paid' => 0,
        ]);
        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $this->postJson('/api/v1/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 150,
            'paid_at' => '2026-01-10',
            'method' => 'cash',
        ])->assertUnprocessable()->assertJsonValidationErrors('amount');
    }

    public function test_generate_creates_invoices_and_is_idempotent(): void
    {
        $tenant = Tenant::factory()->create();
        $customer = Customer::factory()->for($tenant)->create();
        $plan = Plan::factory()->for($tenant)->create(['price' => 200]);
        Subscription::factory()->for($tenant)->create([
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'price' => 200,
            'start_date' => '2026-01-01',
        ]);
        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $this->postJson('/api/v1/invoices/generate', ['month' => '2026-02'])
            ->assertOk()->assertJsonPath('created', 1);

        $this->postJson('/api/v1/invoices/generate', ['month' => '2026-02'])
            ->assertOk()->assertJsonPath('created', 0);

        $this->assertSame(1, $tenant->invoices()->count());
    }

    public function test_generate_skips_subscriptions_starting_after_the_period(): void
    {
        $tenant = Tenant::factory()->create();
        $customer = Customer::factory()->for($tenant)->create();
        $plan = Plan::factory()->for($tenant)->create(['price' => 200]);
        Subscription::factory()->for($tenant)->create([
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'price' => 200,
            'start_date' => '2026-05-01',
        ]);
        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $this->postJson('/api/v1/invoices/generate', ['month' => '2026-02'])
            ->assertOk()->assertJsonPath('created', 0);

        $this->assertSame(0, $tenant->invoices()->count());
        $this->assertLedgerBalanced($tenant);
    }

    public function test_voiding_invoice_reverses_its_entry(): void
    {
        $tenant = Tenant::factory()->create();
        $invoice = Invoice::factory()->for($tenant)->create([
            'customer_id' => Customer::factory()->for($tenant)->create()->id,
            'amount' => 100,
            'status' => InvoiceStatus::Unpaid,
        ]);
        app(LedgerService::class)->recordInvoiceIssued($invoice);
        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $this->deleteJson("/api/v1/invoices/{$invoice->id}")->assertOk();

        $this->assertSame(InvoiceStatus::Void, $invoice->refresh()->status);
        $this->assertEqualsWithDelta(0, $this->balanceOf($tenant, LedgerService::ACCOUNTS_RECEIVABLE), 0.001);
        $this->assertEqualsWithDelta(0, $this->balanceOf($tenant, LedgerService::DEFERRED_REVENUE), 0.001);
        $this->assertLedgerBalanced($tenant);
    }

    public function test_recognizing_revenue_moves_deferred_to_subscription_revenue(): void
    {
        $tenant = Tenant::factory()->create();
        $invoice = Invoice::factory()->for($tenant)->create([
            'customer_id' => Customer::factory()->for($tenant)->create()->id,
            'amount' => 100,
            'status' => InvoiceStatus::Unpaid,
            'period_start' => '2026-03-01',
            'period_end' => '2026-03-31',
            'revenue_recognized_at' => null,
        ]);
        app(LedgerService::class)->recordInvoiceIssued($invoice);
        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $this->postJson('/api/v1/revenue-recognition/recognize', ['month' => '2026-03'])
            ->assertOk()->assertJsonPath('count', 1);

        $this->assertNotNull($invoice->refresh()->revenue_recognized_at);
        $this->assertEqualsWithDelta(0, $this->balanceOf($tenant, LedgerService::DEFERRED_REVENUE), 0.001);
        $this->assertEqualsWithDelta(100, $this->balanceOf($tenant, LedgerService::SUBSCRIPTION_REVENUE), 0.001);
        $this->assertLedgerBalanced($tenant);
    }

    public function test_cannot_pay_invoice_of_another_tenant(): void
    {
        $foreignInvoice = Invoice::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/payments', [
            'invoice_id' => $foreignInvoice->id,
            'amount' => 10,
            'paid_at' => '2026-01-10',
            'method' => 'cash',
        ])->assertUnprocessable()->assertJsonValidationErrors('invoice_id');
    }
}
