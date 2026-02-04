<?php

namespace Database\Factories;

use App\Models\Billing;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Billing>
 */
class BillingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Billing::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->regexify('BILL-[0-9]{4}-[0-9]{4}'),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'year' => fake()->numberBetween(2020, 2026),
            'company_id' => Company::factory(),
            'status' => fake()->randomElement(['active', 'inactive']),
            'is_registered' => fake()->boolean(30),
            'providence' => fake()->randomElement(['Genuis', 'Port-Collect']),
            'type' => fake()->randomElement(['icpe', 'lce', 'port']),
            'payment_state' => fake()->randomElement(['pending', 'partial', 'paid']),
            'edited_at' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
            'last_paid_at' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
