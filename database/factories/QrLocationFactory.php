<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\QrLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<QrLocation>
 */
class QrLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'district_id' => District::factory(),
            'code' => Str::upper(Str::random(10)),
            'name' => 'Taman '.fake()->streetName(),
            'address' => 'Jl. '.fake()->streetName(),
            // Area Kabupaten Jember.
            'latitude' => fake()->latitude(-8.40, -8.05),
            'longitude' => fake()->longitude(113.35, 113.95),
            'is_active' => true,
            'created_by' => User::factory()->adminKabupaten(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
