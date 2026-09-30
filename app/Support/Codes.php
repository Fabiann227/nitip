<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Trip;
use Illuminate\Support\Str;

final class Codes
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function order(): string
    {
        return self::unique('NT-', fn (string $code) => Order::query()->where('code', $code)->exists());
    }

    public static function trip(): string
    {
        return self::unique('TR-', fn (string $code) => Trip::query()->where('code', $code)->exists());
    }

    public static function pin(): string
    {
        return str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public static function otp(int $length = 6): string
    {
        $max = (10 ** $length) - 1;

        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    /**
     * @param  callable(string): bool  $exists
     */
    private static function unique(string $prefix, callable $exists): string
    {
        do {
            $code = $prefix.self::random(6);
        } while ($exists($code));

        return $code;
    }

    private static function random(int $length): string
    {
        $chars = self::ALPHABET;
        $out = '';

        for ($i = 0; $i < $length; $i++) {
            $out .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $out;
    }

    public static function slug(string $value): string
    {
        return Str::slug($value);
    }
}
