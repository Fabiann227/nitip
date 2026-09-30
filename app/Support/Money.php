<?php

namespace App\Support;

final class Money
{
    /**
     * Format an integer rupiah amount, e.g. 3500 => "Rp 3.500".
     */
    public static function rupiah(int|float|string|null $amount, bool $withPrefix = true): string
    {
        $amount = (int) round((float) ($amount ?? 0));
        $formatted = number_format(abs($amount), 0, ',', '.');
        $sign = $amount < 0 ? '-' : '';

        return $withPrefix ? "{$sign}Rp {$formatted}" : "{$sign}{$formatted}";
    }
}
