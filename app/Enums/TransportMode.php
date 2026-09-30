<?php

namespace App\Enums;

enum TransportMode: string
{
    case Walk = 'walk';
    case Bicycle = 'bicycle';
    case Motorcycle = 'motorcycle';
    case Car = 'car';

    public function label(): string
    {
        return match ($this) {
            self::Walk => 'Jalan kaki',
            self::Bicycle => 'Sepeda',
            self::Motorcycle => 'Motor',
            self::Car => 'Mobil',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Walk => 'directions_walk',
            self::Bicycle => 'directions_bike',
            self::Motorcycle => 'two_wheeler',
            self::Car => 'directions_car',
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
