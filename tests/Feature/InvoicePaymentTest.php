<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Assert that the tenant's ledger is balanced (sum of debits equals credits).
     */
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

    public function test_company_sees_only_its_tenant_invoices(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();

        Invoice::factory()->for($tenant)->create([
            'customer_id' => Customer::factory()->for($tenant)->create()->id,
            'invoice_number' => 'INV-9001',
        ]);
        Invoice::factory()->create(['invoice_number' => 'INV-7777']);

        $this->actingAs($user)
            ->get(route('company.invoices'))
            ->assertOk()
            ->assertSee('INV-9001')
            ->assertDontSee('INV-7777');
    }

    public function test_creating_invoice_posts_accounts_receivable_and_deferred_revenue(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $customer = Customer::factory()->for($tenant)->create();

        $this->actingAs($user)->post(route('company.invoices.store'), [
            'customer_id' => $customer->id,
            'issue_date' => '2026-01-01',
            'due_date' => '2026-01-15',
            'amount' => 100,
        ])->assertRedirect(route('company.invoices'));

        $invoice = $tenant->invoices()->first();
        $this->assertNotNull($invoice);
        $this->assertSame(InvoiceStatus::Unpaid, $invoice->status);

        $this->assertEqualsWithDelta(100, $this->balanceOf($tenant, LedgerService::ACCOUNTS_RECEIVABLE), 0.001);
        $this->assertEqualsWithDelta(100, $this->balanceOf($tenant, LedgerService::DEFERRED_REVENUE), 0.001);
        $this->assertLedgerBalanced($tenant);
    }

    public function test_invoice_creation_requires_customer_and_amount(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('company.invoices.store'), ['issue_date' => '2026-01-01', 'due_date' => '2026-01-15'])
            ->assertSessionHasErrors(['customer_id', 'amount']);
    }

    public function test_cannot_invoice_a_customer_from_another_tenant(): void
    {
        $user = User::factory()->create();
        $foreignCustomer = Customer::factory()->for(Tenant::factory())->create();

        $this->actingAs($user)->post(route('company.invoices.store'), [
            'customer_id' => $foreignCustomer->id,
            'issue_date' => '2026-01-01',
            'due_date' => '2026-01-15',
            'amount' => 100,
        ])->assertSessionHasErrors('customer_id');
    }

    public function test_recording_payment_posts_cash_and_settles_invoice(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $customer = Customer::factory()->for($tenant)->create();

        $invoice = Invoice::factory()->for($tenant)->create([
            'customer_id' => $customer->id,
            'amount' => 100,
            'amount_paid' => 0,
            'status' => InvoiceStatus::Unpaid,
        ]);
        app(LedgerService::class)->recordInvoiceIssued($invoice);

        // Partial payment.
        $this->actingAs($user)->post(route('company.payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 40,
            'paid_at' => '2026-01-10',
            'method' => 'cash',
        ])->assertRedirect(route('company.payments'));

        $this->assertSame(InvoiceStatus::PartiallyPaid, $invoice->refresh()->status);
        $this->assertEqualsWithDelta(40, $this->balanceOf($tenant, LedgerService::CASH), 0.001);
        $this->assertEqualsWithDelta(60, $this->balanceOf($tenant, LedgerService::ACCOUNTS_RECEIVABLE), 0.001);

        // Remaining payment.
        $this->actingAs($user)->post(route('company.payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 60,
            'paid_at' => '2026-01-12',
            'method' => 'transfer',
        ])->assertRedirect(route('company.payments'));

        $this->assertSame(InvoiceStatus::Paid, $invoice->refresh()->status);
        $this->assertEqualsWithDelta(100, $this->balanceOf($tenant, LedgerService::CASH), 0.001);
        $this->assertEqualsWithDelta(0, $this->balanceOf($tenant, LedgerService::ACCOUNTS_RECEIVABLE), 0.001);
        $this->assertLedgerBalanced($tenant);
    }

    public function test_payment_cannot_exceed_invoice_balance(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $invoice = Invoice::factory()->for($tenant)->create([
            'customer_id' => Customer::factory()->for($tenant)->create()->id,
            'amount' => 100,
            'amount_paid' => 0,
        ]);

        $this->actingAs($user)->post(route('company.payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 150,
            'paid_at' => '2026-01-10',
            'method' => 'cash',
        ])->assertSessionHasErrors('amount');

        $this->assertSame(0, $invoice->payments()->count());
    }

    public function test_cannot_record_payment_on_void_invoice(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $invoice = Invoice::factory()->for($tenant)->create([
            'customer_id' => Customer::factory()->for($tenant)->create()->id,
            'amount' => 100,
            'status' => InvoiceStatus::Void,
        ]);

        $this->actingAs($user)->post(route('company.payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 50,
            'paid_at' => '2026-01-10',
            'method' => 'cash',
        ])->assertSessionHasErrors('invoice_id');
    }

    public function test_voiding_invoice_reverses_its_entry(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $invoice = Invoice::factory()->for($tenant)->create([
            'customer_id' => Customer::factory()->for($tenant)->create()->id,
            'amount' => 100,
            'status' => InvoiceStatus::Unpaid,
        ]);
        app(LedgerService::class)->recordInvoiceIssued($invoice);

        $this->actingAs($user)->delete(route('company.invoices.void', $invoice))
            ->assertRedirect(route('company.invoices'));

        $this->assertSame(InvoiceStatus::Void, $invoice->refresh()->status);
        $this->assertEqualsWithDelta(0, $this->balanceOf($tenant, LedgerService::ACCOUNTS_RECEIVABLE), 0.001);
        $this->assertEqualsWithDelta(0, $this->balanceOf($tenant, LedgerService::DEFERRED_REVENUE), 0.001);
        $this->assertLedgerBalanced($tenant);
    }

    public function test_cannot_void_invoice_with_payments(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $invoice = Invoice::factory()->for($tenant)->create([
            'customer_id' => Customer::factory()->for($tenant)->create()->id,
            'amount' => 100,
            'amount_paid' => 50,
            'status' => InvoiceStatus::PartiallyPaid,
        ]);
        Payment::factory()->for($tenant)->create([
            'invoice_id' => $invoice->id,
            'customer_id' => $invoice->customer_id,
            'amount' => 50,
        ]);

        $this->actingAs($user)->delete(route('company.invoices.void', $invoice))
            ->assertSessionHas('error');

        $this->assertSame(InvoiceStatus::PartiallyPaid, $invoice->refresh()->status);
    }

    public function test_deleting_payment_reverses_entry_and_recalculates_invoice(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $invoice = Invoice::factory()->for($tenant)->create([
            'customer_id' => Customer::factory()->for($tenant)->create()->id,
            'amount' => 100,
            'amount_paid' => 0,
            'status' => InvoiceStatus::Unpaid,
        ]);
        app(LedgerService::class)->recordInvoiceIssued($invoice);

        $this->actingAs($user)->post(route('company.payments.store'), [
            'invoice_id' => $invoice->id,
            'amount' => 100,
            'paid_at' => '2026-01-10',
            'method' => 'cash',
        ]);

        $payment = $tenant->payments()->first();
        $this->assertSame(InvoiceStatus::Paid, $invoice->refresh()->status);

        $this->actingAs($user)->delete(route('company.payments.destroy', $payment))->assertRedirect();

        $this->assertSame(InvoiceStatus::Unpaid, $invoice->refresh()->status);
        $this->assertEqualsWithDelta(0, $invoice->amount_paid, 0.001);
        $this->assertEqualsWithDelta(0, $this->balanceOf($tenant, LedgerService::CASH), 0.001);
        $this->assertEqualsWithDelta(100, $this->balanceOf($tenant, LedgerService::ACCOUNTS_RECEIVABLE), 0.001);
        $this->assertLedgerBalanced($tenant);
    }

    public function test_generate_creates_invoices_for_active_subscriptions_and_is_idempotent(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $customer = Customer::factory()->for($tenant)->create();
        $plan = Plan::factory()->for($tenant)->create(['price' => 200]);

        Subscription::factory()->for($tenant)->create([
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::Active,
            'price' => 200,
        ]);

        $this->actingAs($user)->post(route('company.invoices.generate'), ['month' => '2026-02'])
            ->assertRedirect(route('company.invoices'));

        $this->assertSame(1, $tenant->invoices()->count());

        // Running again for the same period must not create duplicates.
        $this->actingAs($user)->post(route('company.invoices.generate'), ['month' => '2026-02']);
        $this->assertSame(1, $tenant->invoices()->count());

        $this->assertEqualsWithDelta(200, $this->balanceOf($tenant, LedgerService::ACCOUNTS_RECEIVABLE), 0.001);
        $this->assertLedgerBalanced($tenant);
    }

    public function test_cannot_pay_invoice_of_another_tenant(): void
    {
        $user = User::factory()->create();
        $foreignInvoice = Invoice::factory()->create();

        $this->actingAs($user)->post(route('company.payments.store'), [
            'invoice_id' => $foreignInvoice->id,
            'amount' => 10,
            'paid_at' => '2026-01-10',
            'method' => 'cash',
        ])->assertSessionHasErrors('invoice_id');
    }

    public function test_cannot_void_invoice_of_another_tenant(): void
    {
        $user = User::factory()->create();
        $foreignInvoice = Invoice::factory()->create();

        $this->actingAs($user)->delete(route('company.invoices.void', $foreignInvoice))
            ->assertForbidden();
    }

    public function test_cannot_delete_payment_of_another_tenant(): void
    {
        $user = User::factory()->create();
        $foreignPayment = Payment::factory()->create();

        $this->actingAs($user)->delete(route('company.payments.destroy', $foreignPayment))
            ->assertForbidden();
    }
}
