<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@wadulin.test',
            'role' => UserRole::Admin,
        ]);

        User::factory()->create([
            'name' => 'Petugas Lapangan',
            'email' => 'petugas@wadulin.test',
            'role' => UserRole::Petugas,
        ]);

        $this->call(PengaduanSeeder::class);
    }
}
