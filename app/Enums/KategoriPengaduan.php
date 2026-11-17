<?php

namespace App\Enums;

enum KategoriPengaduan: string
{
    case Infrastruktur = 'infrastruktur';
    case Kebersihan = 'kebersihan';
    case Keamanan = 'keamanan';
    case Kesehatan = 'kesehatan';
    case Pelayanan = 'pelayanan';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Infrastruktur => 'Infrastruktur',
            self::Kebersihan => 'Kebersihan',
            self::Keamanan => 'Keamanan',
            self::Kesehatan => 'Kesehatan',
            self::Pelayanan => 'Pelayanan Publik',
            self::Lainnya => 'Lainnya',
        };
    }
}
