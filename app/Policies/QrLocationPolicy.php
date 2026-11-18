<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\QrLocation;
use App\Models\User;

class QrLocationPolicy
{
    /**
     * District officers manage their own district's QR points; the regency manages all.
     */
    public function update(User $user, QrLocation $qrLocation): bool
    {
        return match ($user->role) {
            UserRole::AdminKabupaten => true,
            UserRole::AdminKecamatan => $qrLocation->district_id === $user->district_id,
            UserRole::Masyarakat => false,
        };
    }
}
