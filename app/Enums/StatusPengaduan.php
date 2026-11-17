<?php

namespace App\Enums;

enum StatusPengaduan: string
{
    case Pending = 'pending';
    case Proses = 'proses';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Diajukan',
            self::Proses => 'Diproses',
            self::Selesai => 'Selesai',
        };
    }

    /** Hex color used by the Leaflet map markers on the dashboard. */
    public function hexColor(): string
    {
        return match ($this) {
            self::Pending => '#f59e0b',
            self::Proses => '#0ea5e9',
            self::Selesai => '#10b981',
        };
    }
}
