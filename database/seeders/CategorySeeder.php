<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed the complaint categories (Lampiran B.2).
     */
    public function run(): void
    {
        $categories = [
            ['Jalan & Jembatan', 'construction', 'Jalan berlubang, jembatan retak'],
            ['Drainase & Banjir', 'waves', 'Selokan tersumbat, genangan'],
            ['Sampah & Kebersihan', 'trash-2', 'Sampah menumpuk, TPS penuh'],
            ['Penerangan Jalan', 'lamp', 'PJU mati'],
            ['Air Bersih', 'droplets', 'Air PDAM tidak mengalir'],
            ['Fasilitas Umum', 'trees', 'Taman, halte, toilet umum rusak'],
            ['Kesehatan', 'heart-pulse', 'Layanan puskesmas'],
            ['Pendidikan', 'graduation-cap', 'Sarana sekolah'],
            ['Administrasi Kependudukan', 'id-card', 'KTP, KK, layanan desa/kecamatan'],
            ['Ketertiban Umum', 'shield', 'Parkir liar, PKL mengganggu'],
            ['Lainnya', 'circle-help', 'Di luar kategori di atas'],
        ];

        foreach ($categories as [$name, $icon, $description]) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'icon' => $icon, 'description' => $description, 'is_active' => true],
            );
        }
    }
}
