<?php

use App\Support\Money;
use App\Support\Phone;

if (! function_exists('rupiah')) {
    function rupiah(int|float|string|null $amount, bool $withPrefix = true): string
    {
        return Money::rupiah($amount, $withPrefix);
    }
}

if (! function_exists('phone_pretty')) {
    function phone_pretty(?string $number): string
    {
        return Phone::pretty($number);
    }
}
