<?php

namespace App\Enums;

enum DisputeReason: string
{
    case NotDelivered = 'not_delivered';
    case WrongItem = 'wrong_item';
    case ReceiptMismatch = 'receipt_mismatch';
    case PaymentIssue = 'payment_issue';
    case Unresponsive = 'unresponsive';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::NotDelivered => 'Pesanan tidak diserahkan',
            self::WrongItem => 'Barang / hasil cetak tidak sesuai',
            self::ReceiptMismatch => 'Selisih nota / biaya tidak sesuai',
            self::PaymentIssue => 'Masalah pembayaran',
            self::Unresponsive => 'Pihak lain tidak merespons',
            self::Other => 'Lainnya',
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
