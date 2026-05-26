<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MarketingOrder>
 */
class MarketingOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sap_no' => $this->faker->unique()->numberBetween(100000, 999999),
            'art_no' => $this->faker->unique()->numerify('ART-#####'),
            'tanggal' => now(),
            'pelanggan' => $this->faker->company(),
            'warna' => $this->faker->colorName(),
            'kg_target' => $this->faker->numberBetween(100, 1000),
            'roll_target' => $this->faker->numberBetween(10, 50),
            'mkt' => $this->faker->name(),
            'keperluan' => 'R&D',
            'konstruksi_greige' => 'Single Jersey',
            'material' => 'Cotton',
            'benang' => '30s',
            'kelompok_kain' => $this->faker->randomElement(['UNIT 1', 'UNIT 2', 'UNIT 3']),
            'target_lebar' => '72"',
            'target_gramasi' => '150',
            'belah_bulat' => 'Bulat',
            'handfeel' => 'Soft',
            'status' => 'knitting',
            'is_urgent' => false,
            'req_stenter' => true,
            'req_compactor' => false,
            'req_heat_setting' => false,
            'req_tumbler' => false,
            'req_fleece' => false,
            'req_pengujian' => true,
            'req_qe' => true,
        ];
    }

    public function urgent()
    {
        return $this->state(fn (array $attributes) => [
            'is_urgent' => true,
        ]);
    }
}
