<?php

use App\Models\Tenant;
use App\Services\LedgerService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $ledger = app(LedgerService::class);

        Tenant::query()->each(function (Tenant $tenant) use ($ledger) {
            $ledger->seedChartOfAccounts($tenant);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Accounts are removed automatically when their tenant is deleted.
    }
};
