<?php

namespace Database\Seeders;

use App\Enums\TenantStatus;
use App\Enums\UserRole;
use App\Models\Tenant;
use App\Services\LedgerService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        $tenant = Tenant::firstOrCreate(
            ['name' => 'Test Company LLC'],
            [
                'email' => 'company@accrualhub.test',
                'phone' => '+966500000000',
                'status' => TenantStatus::Active,
            ],
        );

        $tenant->users()->firstOrCreate(
            ['email' => 'company@accrualhub.test'],
            [
                'name' => 'Test Company',
                'password' => 'password',
                'role' => UserRole::Company,
                'is_owner' => true,
                'email_verified_at' => now(),
            ],
        );

        app(LedgerService::class)->seedChartOfAccounts($tenant);

        $this->call(PlanSeeder::class);
    }
}
