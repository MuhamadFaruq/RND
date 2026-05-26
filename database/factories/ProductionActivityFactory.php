<?php

namespace Database\Factories;

use App\Models\MarketingOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductionActivity>
 */
class ProductionActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'marketing_order_id' => MarketingOrder::factory(),
            'operator_id' => User::factory(),
            'division_name' => 'knitting',
            'kg' => $this->faker->randomFloat(2, 50, 500),
            'roll' => $this->faker->numberBetween(1, 20),
            'shift' => $this->faker->numberBetween(1, 3),
            'technical_data' => ['notes' => 'Some technical data'],
        ];
    }
}
