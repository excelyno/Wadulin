<?php

namespace Database\Factories;

use App\Enums\KategoriPengaduan;
use App\Enums\StatusPengaduan;
use App\Models\Pengaduan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pengaduan>
 */
class PengaduanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Seed locations scattered around the Greater Jakarta area so the live
        // map shows a realistic cluster without any external geocoding service.
        return [
            'kode_pengaduan' => 'PDG-'.fake()->date('ymd').'-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
            'nama_pelapor' => fake()->name(),
            'kontak_pelapor' => fake()->optional(0.7)->phoneNumber(),
            'judul' => fake()->randomElement([
                'Jalan berlubang parah',
                'Lampu penerangan jalan mati',
                'Sampah menumpuk di pinggir jalan',
                'Saluran air tersumbat',
                'Banjir genangan di permukiman',
                'Trotoar rusak dan berbahaya',
                'Tiang listrik miring',
                'Pohon tumbang menutup jalan',
                'Air bersih tidak mengalir',
                'Fasilitas umum rusak',
                'Kemacetan di persimpangan',
                'Jembatan rusak',
                'Fasilitas kesehatan kotor',
                'Pelayanan publik lambat',
                'Pungutan liar di lokasi',
                'Kebisingan pabrik',
                'Banjir rob di pesisir',
                'Akses jalan putus',
            ]),
            'deskripsi' => fake()->paragraph(),
            'kategori' => fake()->randomElement(KategoriPengaduan::cases()),
            'alamat' => 'Jl. '.fake()->streetName().' No. '.random_int(1, 200).', '.fake()->city(),
            'latitude' => fake()->latitude(-6.35, -6.05),
            'longitude' => fake()->longitude(106.65, 106.95),
            'status' => fake()->randomElement(StatusPengaduan::cases()),
            'foto' => null,
        ];
    }
}
