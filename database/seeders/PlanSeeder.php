<?php

namespace Database\Seeders;

use App\Enums\BillingCycle;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Seed demo subscription plans for the demo tenant.
     */
    public function run(): void
    {
        $tenant = Tenant::firstWhere('name', 'Test Company LLC');

        if ($tenant === null) {
            return;
        }

        $plans = [
            [
                'name' => 'الباقة الأساسية',
                'description' => 'مناسبة للشركات الناشئة التي تبدأ رحلتها.',
                'price' => 99,
                'billing_cycle' => BillingCycle::Monthly,
                'features' => ['حتى 50 عميلاً', 'فواتير تلقائية', 'تقرير شهري'],
                'is_active' => true,
            ],
            [
                'name' => 'الباقة الاحترافية',
                'description' => 'الأكثر شيوعاً للشركات المتوسطة.',
                'price' => 299,
                'billing_cycle' => BillingCycle::Monthly,
                'features' => ['عملاء غير محدودين', 'تقارير لحظية', 'مستخدمون متعددون', 'دعم ذو أولوية'],
                'is_active' => true,
            ],
            [
                'name' => 'باقة المؤسسات',
                'description' => 'حلول متكاملة للمؤسسات الكبيرة.',
                'price' => 2400,
                'billing_cycle' => BillingCycle::Yearly,
                'features' => ['كل مزايا الاحترافية', 'تكامل مع البنوك', 'مدير حساب مخصص'],
                'is_active' => false,
            ],
        ];

        foreach ($plans as $plan) {
            $tenant->plans()->updateOrCreate(['name' => $plan['name']], $plan + ['currency' => 'SAR']);
        }
    }
}
