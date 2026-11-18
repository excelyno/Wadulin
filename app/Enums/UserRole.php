<?php

namespace App\Enums;

enum UserRole: string
{
    case Masyarakat = 'masyarakat';
    case AdminKecamatan = 'admin_kecamatan';
    case AdminKabupaten = 'admin_kabupaten';

    public function label(): string
    {
        return match ($this) {
            self::Masyarakat => 'Masyarakat',
            self::AdminKecamatan => 'Admin Kecamatan',
            self::AdminKabupaten => 'Admin Kabupaten',
        };
    }

    /**
     * Route-name prefix of the admin panel for this role, if any.
     */
    public function panelPrefix(): ?string
    {
        return match ($this) {
            self::AdminKecamatan => 'kecamatan',
            self::AdminKabupaten => 'kabupaten',
            self::Masyarakat => null,
        };
    }

    /**
     * Named route a user of this role lands on after logging in.
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Masyarakat => 'dashboard',
            self::AdminKecamatan => 'kecamatan.dashboard',
            self::AdminKabupaten => 'kabupaten.dashboard',
        };
    }
}
