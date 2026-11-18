<?php

namespace App\Policies;

use App\Enums\ComplaintStatus;
use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    /**
     * Owners see their own complaints, district officers see their district,
     * and regency officers see everything.
     */
    public function view(User $user, Complaint $complaint): bool
    {
        return match ($user->role) {
            UserRole::Masyarakat => $complaint->user_id === $user->id,
            UserRole::AdminKecamatan => $complaint->district_id === $user->district_id,
            UserRole::AdminKabupaten => true,
        };
    }

    /**
     * District officers handle their own district; the regency only steps in
     * once a complaint has been escalated.
     */
    public function updateStatus(User $user, Complaint $complaint): bool
    {
        if ($complaint->status->isFinal()) {
            return false;
        }

        return match ($user->role) {
            UserRole::AdminKecamatan => $complaint->district_id === $user->district_id,
            UserRole::AdminKabupaten => $complaint->isEscalated(),
            UserRole::Masyarakat => false,
        };
    }

    /**
     * Reporters may withdraw their complaint until an officer starts on it.
     */
    public function cancel(User $user, Complaint $complaint): bool
    {
        return $complaint->user_id === $user->id
            && $complaint->status === ComplaintStatus::Pending;
    }

    /**
     * Reporters rate a finished complaint once.
     */
    public function rate(User $user, Complaint $complaint): bool
    {
        return $complaint->user_id === $user->id
            && $complaint->status === ComplaintStatus::Selesai
            && $complaint->rating === null;
    }

    /**
     * A district may only hand over complaints it has not started on yet.
     */
    public function dispose(User $user, Complaint $complaint): bool
    {
        return match ($user->role) {
            UserRole::AdminKecamatan => $complaint->district_id === $user->district_id
                && $complaint->status === ComplaintStatus::Pending,
            UserRole::AdminKabupaten => $complaint->isEscalated(),
            UserRole::Masyarakat => false,
        };
    }
}
