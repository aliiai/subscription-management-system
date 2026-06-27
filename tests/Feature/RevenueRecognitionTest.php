<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RevenueRecognitionTest extends TestCase
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

    /**
     * Create an issued invoice (with its A/R + Deferred Revenue entry) for the given period.
     */
    protected function issuedInvoice(Tenant $tenant, Carbon $period, array $attributes = []): Invoice
    {
        $invoice = Invoice::factory()->for($tenant)->create(array_merge([
            'customer_id' => Customer::factory()->for($tenant)->create()->id,
            'amount' => 100,
            'period_start' => $period->copy()->startOfMonth(),
            'period_end' => $period->copy()->endOfMonth(),
            'status' => InvoiceStatus::Unpaid,
        ], $attributes));

        app(LedgerService::class)->recordInvoiceIssued($invoice);

        return $invoice;
    }

    public function test_recognition_moves_deferred_revenue_to_subscription_revenue(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $this->issuedInvoice($tenant, Carbon::parse('2026-02-15'));

        $this->actingAs($user)->post(route('company.revenue-recognition.recognize'), ['month' => '2026-02'])
            ->assertRedirect();

        $this->assertEqualsWithDelta(0, $this->balanceOf($tenant, LedgerService::DEFERRED_REVENUE), 0.001);
        $this->assertEqualsWithDelta(100, $this->balanceOf($tenant, LedgerService::SUBSCRIPTION_REVENUE), 0.001);
        $this->assertLedgerBalanced($tenant);
        $this->assertNotNull($tenant->invoices()->first()->revenue_recognized_at);
    }

    public function test_recognition_is_independent_of_payment_status(): void
    {
        $tenant = Tenant::factory()->create();
        $invoice = $this->issuedInvoice($tenant, Carbon::parse('2026-02-10'));

        $result = app(\App\Services\RecognitionService::class)
            ->recognizeForTenant($tenant, Carbon::parse('2026-02-01'));

        $this->assertSame(1, $result['count']);
        $this->assertEqualsWithDelta(100, $result['amount'], 0.001);
        $this->assertSame(InvoiceStatus::Unpaid, $invoice->refresh()->status);
        $this->assertEqualsWithDelta(100, $this->balanceOf($tenant, LedgerService::SUBSCRIPTION_REVENUE), 0.001);
    }

    public function test_voided_invoices_are_skipped(): void
    {
        $tenant = Tenant::factory()->create();
        Invoice::factory()->for($tenant)->create([
            'customer_id' => Customer::factory()->for($tenant)->create()->id,
            'amount' => 100,
            'period_start' => '2026-02-01',
            'period_end' => '2026-02-28',
            'status' => InvoiceStatus::Void,
        ]);

        $result = app(\App\Services\RecognitionService::class)
            ->recognizeForTenant($tenant, Carbon::parse('2026-02-01'));

        $this->assertSame(0, $result['count']);
        $this->assertEqualsWithDelta(0, $this->balanceOf($tenant, LedgerService::SUBSCRIPTION_REVENUE), 0.001);
    }

    public function test_recognition_is_idempotent(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $this->issuedInvoice($tenant, Carbon::parse('2026-02-15'));

        $this->actingAs($user)->post(route('company.revenue-recognition.recognize'), ['month' => '2026-02']);
        $this->actingAs($user)->post(route('company.revenue-recognition.recognize'), ['month' => '2026-02']);

        // Subscription revenue must only reflect a single recognition.
        $this->assertEqualsWithDelta(100, $this->balanceOf($tenant, LedgerService::SUBSCRIPTION_REVENUE), 0.001);
        $this->assertLedgerBalanced($tenant);
    }

    public function test_only_invoices_whose_period_ends_in_target_month_are_recognized(): void
    {
        $tenant = Tenant::factory()->create();
        $this->issuedInvoice($tenant, Carbon::parse('2026-02-15'), ['amount' => 100]);
        $this->issuedInvoice($tenant, Carbon::parse('2026-03-15'), ['amount' => 250]);

        $result = app(\App\Services\RecognitionService::class)
            ->recognizeForTenant($tenant, Carbon::parse('2026-02-01'));

        $this->assertSame(1, $result['count']);
        $this->assertEqualsWithDelta(100, $this->balanceOf($tenant, LedgerService::SUBSCRIPTION_REVENUE), 0.001);
        $this->assertEqualsWithDelta(250, $this->balanceOf($tenant, LedgerService::DEFERRED_REVENUE), 0.001);
    }

    public function test_recognition_is_tenant_isolated(): void
    {
        $tenant = Tenant::factory()->create();
        $other = Tenant::factory()->create();
        $this->issuedInvoice($other, Carbon::parse('2026-02-15'));

        $result = app(\App\Services\RecognitionService::class)
            ->recognizeForTenant($tenant, Carbon::parse('2026-02-01'));

        $this->assertSame(0, $result['count']);
        $this->assertEqualsWithDelta(0, $this->balanceOf($tenant, LedgerService::SUBSCRIPTION_REVENUE), 0.001);
        $this->assertEqualsWithDelta(100, $this->balanceOf($other, LedgerService::DEFERRED_REVENUE), 0.001);
    }

    public function test_company_sees_only_its_pending_invoices_on_page(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->for($tenant)->create();
        $this->issuedInvoice($tenant, Carbon::parse('2026-02-15'), ['invoice_number' => 'INV-5001']);
        $this->issuedInvoice(Tenant::factory()->create(), Carbon::parse('2026-02-15'), ['invoice_number' => 'INV-6002']);

        $this->actingAs($user)
            ->get(route('company.revenue-recognition', ['month' => '2026-02']))
            ->assertOk()
            ->assertSee('INV-5001')
            ->assertDontSee('INV-6002');
    }
}
