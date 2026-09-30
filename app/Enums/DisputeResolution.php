<?php

namespace App\Enums;

enum DisputeResolution: string
{
    case CompleteOrder = 'complete_order';
    case CancelOrder = 'cancel_order';
    case CancelWithRefund = 'cancel_with_refund';

    public function label(): string
    {
        return match ($this) {
            self::CompleteOrder => 'Tandai pesanan selesai (relawan berhak atas pembayaran)',
            self::CancelOrder => 'Batalkan pesanan tanpa refund',
            self::CancelWithRefund => 'Batalkan pesanan dan wajibkan refund ke penitip',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::CompleteOrder => 'Pesanan diselesaikan',
            self::CancelOrder => 'Dibatalkan',
            self::CancelWithRefund => 'Dibatalkan + refund',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }
}
