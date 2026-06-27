<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IsolationAndReportsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_listing_is_scoped_to_the_tenant(): void
    {
        $tenant = Tenant::factory()->create();
        Customer::factory()->for($tenant)->create(['name' => 'عميلي']);
        Customer::factory()->for(Tenant::factory())->create(['name' => 'عميل آخر']);

        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $this->getJson('/api/v1/customers')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'عميلي');
    }

    public function test_cannot_view_another_tenant_invoice(): void
    {
        $foreign = Invoice::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/v1/invoices/{$foreign->id}")->assertNotFound();
    }

    public function test_accounts_endpoint_returns_seeded_chart_with_balances(): void
    {
        $tenant = Tenant::factory()->create();
        app(LedgerService::class)->seedChartOfAccounts($tenant);
        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $this->getJson('/api/v1/accounts')
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonStructure(['data' => [['code', 'name', 'type', 'balance']]]);
    }

    public function test_income_statement_and_balance_sheet_endpoints_respond(): void
    {
        $tenant = Tenant::factory()->create();
        $invoice = Invoice::factory()->for($tenant)->create([
            'customer_id' => Customer::factory()->for($tenant)->create()->id,
            'amount' => 100,
        ]);
        app(LedgerService::class)->recordInvoiceIssued($invoice);
        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $this->getJson('/api/v1/reports/balance-sheet')
            ->assertOk()
            ->assertJsonPath('data.balanced', true)
            ->assertJsonPath('data.total_assets', 100);

        $this->getJson('/api/v1/reports/income-statement?from=2000-01-01&to=2100-01-01')
            ->assertOk()
            ->assertJsonStructure(['data' => ['total_revenue', 'net_income']]);
    }

    public function test_dashboard_endpoint_responds(): void
    {
        $tenant = Tenant::factory()->create();
        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $this->getJson('/api/v1/dashboard')->assertOk()->assertJsonStructure(['data']);
    }

    public function test_settings_endpoint_returns_company_and_user(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'شركتي']);
        Sanctum::actingAs(User::factory()->for($tenant)->create());

        $this->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonPath('company.name', 'شركتي')
            ->assertJsonStructure(['user' => ['id', 'email']]);
    }
}
