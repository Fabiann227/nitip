<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Open = 'open';
    case AwaitingPayment = 'awaiting_payment';
    case PaymentSubmitted = 'payment_submitted';
    case Paid = 'paid';
    case InProgress = 'in_progress';
    case Delivering = 'delivering';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Disputed = 'disputed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Mencari Relawan',
            self::AwaitingPayment => 'Menunggu Pembayaran',
            self::PaymentSubmitted => 'Verifikasi Pembayaran',
            self::Paid => 'Dibayar',
            self::InProgress => 'Sedang Diproses',
            self::Delivering => 'Sedang Diantar',
            self::Delivered => 'Sudah Diserahkan',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::Disputed => 'Sengketa',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Open => 'Permintaan sudah tayang dan menunggu relawan yang searah untuk mengambilnya.',
            self::AwaitingPayment => 'Relawan sudah cocok. Penitip perlu transfer dan unggah bukti pembayaran.',
            self::PaymentSubmitted => 'Bukti transfer sudah diunggah dan menunggu verifikasi relawan.',
            self::Paid => 'Pembayaran terverifikasi. Relawan akan segera memproses pesanan.',
            self::InProgress => 'Relawan sedang membeli / mencetak pesanan.',
            self::Delivering => 'Pesanan sedang bergerak menuju titik serah terima.',
            self::Delivered => 'Relawan sudah menyerahkan pesanan. Penitip perlu konfirmasi.',
            self::Completed => 'Transaksi selesai. Terima kasih sudah memakai Nitip!',
            self::Cancelled => 'Pesanan dibatalkan.',
            self::Disputed => 'Sengketa sedang ditinjau admin Nitip.',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'badge-info',
            self::AwaitingPayment, self::PaymentSubmitted => 'badge-warning',
            self::Paid, self::Delivered, self::Completed => 'badge-success',
            self::InProgress, self::Delivering => 'badge-secondary',
            self::Cancelled, self::Disputed => 'badge-danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Open => 'campaign',
            self::AwaitingPayment => 'payments',
            self::PaymentSubmitted => 'receipt_long',
            self::Paid => 'verified',
            self::InProgress => 'shopping_cart_checkout',
            self::Delivering => 'directions_walk',
            self::Delivered => 'handshake',
            self::Completed => 'task_alt',
            self::Cancelled => 'cancel',
            self::Disputed => 'gavel',
        };
    }

    /**
     * Step index (1..5) on the PDF lifecycle:
     * 1 Post & Match, 2 Pay & Verify, 3 In Progress, 4 Delivering, 5 Completed.
     */
    public function step(): ?int
    {
        return match ($this) {
            self::Open => 1,
            self::AwaitingPayment, self::PaymentSubmitted => 2,
            self::Paid, self::InProgress => 3,
            self::Delivering => 4,
            self::Delivered, self::Completed => 5,
            self::Cancelled, self::Disputed => null,
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }

    public function isActive(): bool
    {
        return ! $this->isTerminal();
    }

    public function isMatched(): bool
    {
        return $this !== self::Open;
    }

    /**
     * Statuses that count as "matched & alive" for trip slot usage.
     *
     * @return list<self>
     */
    public static function occupyingSlot(): array
    {
        return [
            self::AwaitingPayment,
            self::PaymentSubmitted,
            self::Paid,
            self::InProgress,
            self::Delivering,
            self::Delivered,
            self::Disputed,
        ];
    }

    /**
     * @return list<self>
     */
    public static function activeStatuses(): array
    {
        return array_values(array_filter(self::cases(), fn (self $s) => $s->isActive()));
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }
}
