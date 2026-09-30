<?php

namespace App\Enums;

enum PaymentMethodType: string
{
    case Gopay = 'gopay';
    case Ovo = 'ovo';
    case Dana = 'dana';
    case Shopeepay = 'shopeepay';
    case BankTransfer = 'bank_transfer';
    case Qris = 'qris';

    public function label(): string
    {
        return match ($this) {
            self::Gopay => 'GoPay',
            self::Ovo => 'OVO',
            self::Dana => 'DANA',
            self::Shopeepay => 'ShopeePay',
            self::BankTransfer => 'Transfer Bank',
            self::Qris => 'QRIS',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BankTransfer => 'account_balance',
            self::Qris => 'qr_code_2',
            default => 'account_balance_wallet',
        };
    }

    public function isEwallet(): bool
    {
        return in_array($this, [self::Gopay, self::Ovo, self::Dana, self::Shopeepay], true);
    }

    public function requiresAccountNumber(): bool
    {
        return $this !== self::Qris;
    }

    public function requiresProviderName(): bool
    {
        return $this === self::BankTransfer;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }
}
