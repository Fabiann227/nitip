<?php

namespace App\Enums;

enum TripStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Terbuka',
            self::Closed => 'Ditutup',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'badge-success',
            self::Closed => 'badge-neutral',
            self::Cancelled => 'badge-danger',
        };
    }
}
