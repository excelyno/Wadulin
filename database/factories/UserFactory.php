<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\District;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone' => '08'.fake()->numerify('##########'),
            'role' => UserRole::Masyarakat,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Officer assigned to a single district.
     */
    public function adminKecamatan(?District $district = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::AdminKecamatan,
            'district_id' => $district?->id ?? District::factory(),
        ]);
    }

    /**
     * Regency-level officer who oversees every district.
     */
    public function adminKabupaten(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::AdminKabupaten,
            'district_id' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
