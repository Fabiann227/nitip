<?php

namespace App\Support;

final class Phone
{
    /**
     * Normalise an Indonesian phone number to international format without "+" (62xxxx).
     */
    public static function normalize(?string $number): ?string
    {
        if ($number === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return $digits;
    }

    /**
     * Human readable local format, e.g. 0812-3456-7890.
     */
    public static function pretty(?string $number): string
    {
        $normalized = self::normalize($number);

        if ($normalized === null) {
            return '-';
        }

        $local = '0'.substr($normalized, 2);

        return trim(chunk_split($local, 4, '-'), '-');
    }

    public static function whatsappUrl(?string $number, ?string $text = null): ?string
    {
        $normalized = self::normalize($number);

        if ($normalized === null) {
            return null;
        }

        $url = "https://wa.me/{$normalized}";

        if ($text !== null && $text !== '') {
            $url .= '?text='.rawurlencode($text);
        }

        return $url;
    }
}
