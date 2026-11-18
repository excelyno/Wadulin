<?php

namespace Database\Factories;

use App\Enums\ComplaintStatus;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\District;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-60 days');

        return [
            'ticket_code' => 'WDL-'.now()->year.'-'.fake()->unique()->numerify('######'),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'district_id' => District::factory(),
            'qr_location_id' => null,
            'title' => fake()->randomElement([
                'Jalan berlubang parah di depan sekolah',
                'Lampu penerangan jalan mati sejak seminggu',
                'Sampah menumpuk di pinggir jalan desa',
                'Saluran air tersumbat saat hujan',
                'Genangan banjir di permukiman warga',
                'Air PDAM tidak mengalir tiga hari',
                'Halte bus rusak dan atap bocor',
                'Parkir liar menutup bahu jalan',
            ]),
            'description' => fake()->paragraph(3),
            'address' => 'Jl. '.fake()->streetName().' No. '.fake()->numberBetween(1, 200),
            // Area Kabupaten Jember.
            'latitude' => fake()->latitude(-8.40, -8.05),
            'longitude' => fake()->longitude(113.35, 113.95),
            'status' => ComplaintStatus::Pending,
            'is_anonymous' => fake()->boolean(20),
            'due_at' => Complaint::calculateDueAt(now()->setTimestamp($createdAt->getTimestamp())),
            'escalated_at' => null,
            'resolved_at' => null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    public function withStatus(ComplaintStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'resolved_at' => $status->isFinal() ? now() : null,
        ]);
    }

    /**
     * An open complaint that has passed its SLA and been flagged for the regency.
     */
    public function escalated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ComplaintStatus::Pending,
            'due_at' => now()->subDays(2),
            'escalated_at' => now()->subDay(),
        ]);
    }
}
