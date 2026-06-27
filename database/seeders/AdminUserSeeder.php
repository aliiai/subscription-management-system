<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Provision the default system administrator account.
     *
     * Admin accounts cannot be created through the registration page, so they
     * are seeded here instead.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@accrualhub.test'],
            [
                'name' => 'System Administrator',
                'password' => '12345678',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ],
        );
    }
}
