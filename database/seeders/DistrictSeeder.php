<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Seed the 31 districts of Kabupaten Jember (Lampiran B.1).
     */
    public function run(): void
    {
        $names = [
            'Ajung', 'Ambulu', 'Arjasa', 'Balung', 'Bangsalsari', 'Gumukmas', 'Jelbuk', 'Jenggawah',
            'Jombang', 'Kalisat', 'Kaliwates', 'Kencong', 'Ledokombo', 'Mayang', 'Mumbulsari', 'Pakusari',
            'Panti', 'Patrang', 'Puger', 'Rambipuji', 'Semboro', 'Silo', 'Sukorambi', 'Sukowono',
            'Sumberbaru', 'Sumberjambe', 'Sumbersari', 'Tanggul', 'Tempurejo', 'Umbulsari', 'Wuluhan',
        ];

        foreach ($names as $index => $name) {
            District::updateOrCreate(
                ['name' => $name],
                ['code' => 'JBR-'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)],
            );
        }
    }
}
