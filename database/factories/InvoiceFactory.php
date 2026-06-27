<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tenant = Tenant::factory()->create();
        $issueDate = Carbon::parse(fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'));

        return [
            'tenant_id' => $tenant->id,
            'customer_id' => Customer::factory()->for($tenant),
            'subscription_id' => null,
            'invoice_number' => 'INV-'.fake()->unique()->numerify('#####'),
            'issue_date' => $issueDate,
            'due_date' => $issueDate->copy()->addDays(14),
            'period_start' => $issueDate->copy()->startOfMonth(),
            'period_end' => $issueDate->copy()->endOfMonth(),
            'amount' => fake()->randomElement([100, 250, 500, 800]),
            'amount_paid' => 0,
            'currency' => 'SAR',
            'status' => InvoiceStatus::Unpaid,
        ];
    }

    /**
     * Indicate that the invoice has been fully paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount_paid' => $attributes['amount'],
            'status' => InvoiceStatus::Paid,
        ]);
    }

    /**
     * Indicate that the invoice's revenue has already been recognized.
     */
    public function recognized(): static
    {
        return $this->state(fn (array $attributes) => [
            'revenue_recognized_at' => now(),
        ]);
    }
}
