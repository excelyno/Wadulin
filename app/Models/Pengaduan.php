<?php

namespace App\Models;

use App\Enums\KategoriPengaduan;
use App\Enums\StatusPengaduan;
use Database\Factories\PengaduanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'kode_pengaduan',
    'nama_pelapor',
    'kontak_pelapor',
    'judul',
    'deskripsi',
    'kategori',
    'alamat',
    'latitude',
    'longitude',
    'status',
    'foto',
])]
class Pengaduan extends Model
{
    /** @use HasFactory<PengaduanFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kategori' => KategoriPengaduan::class,
            'status' => StatusPengaduan::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /**
     * Build a unique human-readable tracking code, e.g. PDG-261116-0042.
     */
    public static function generateKode(): string
    {
        do {
            $kode = 'PDG-'.now()->format('ymd').'-'.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('kode_pengaduan', $kode)->exists());

        return $kode;
    }
}
