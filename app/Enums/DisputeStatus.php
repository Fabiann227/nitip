<?php

namespace App\Enums;

enum DisputeStatus: string
{
    case Open = 'open';
    case Resolved = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Menunggu Tinjauan',
            self::Resolved => 'Selesai',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'badge-warning',
            self::Resolved => 'badge-success',
        };
    }
}
