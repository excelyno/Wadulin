<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case Pending = 'pending';
    case Diproses = 'diproses';
    case Selesai = 'selesai';
    case Ditolak = 'ditolak';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Diproses => 'Diproses',
            self::Selesai => 'Selesai',
            self::Ditolak => 'Ditolak',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    /**
     * Final statuses can never change again.
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::Selesai, self::Ditolak, self::Dibatalkan], true);
    }

    /**
     * Statuses an officer may move a complaint to from the current status.
     *
     * @return list<self>
     */
    public function officerTransitions(): array
    {
        return match ($this) {
            self::Pending => [self::Diproses, self::Ditolak],
            self::Diproses => [self::Selesai, self::Ditolak],
            default => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->officerTransitions(), true);
    }

    /**
     * Statuses that still count against the SLA.
     *
     * @return list<self>
     */
    public static function open(): array
    {
        return [self::Pending, self::Diproses];
    }

    /** Hex color for map markers. */
    public function hexColor(): string
    {
        return match ($this) {
            self::Pending => '#B45309',
            self::Diproses => '#1D4ED8',
            self::Selesai => '#15803D',
            self::Ditolak => '#B91C1C',
            self::Dibatalkan => '#475569',
        };
    }
}
