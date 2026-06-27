<?php

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->randomElement(['الأساسية', 'الاحترافية', 'المؤسسات', 'المتقدمة']),
            'description' => fake()->sentence(),
            'price' => fake()->randomElement([99, 199, 399, 799]),
            'currency' => 'SAR',
            'billing_cycle' => fake()->randomElement(BillingCycle::cases()),
            'features' => fake()->randomElements([
                'عدد عملاء غير محدود',
                'فواتير تلقائية',
                'تقارير مالية لحظية',
                'دعم فني على مدار الساعة',
                'تكامل مع البنوك',
                'مستخدمون متعددون',
            ], 3),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the plan is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
