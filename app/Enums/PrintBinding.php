<?php

namespace App\Enums;

enum PrintBinding: string
{
    case None = 'none';
    case Staple = 'staple';
    case Spiral = 'spiral';
    case Softcover = 'softcover';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Tanpa jilid',
            self::Staple => 'Staples',
            self::Spiral => 'Jilid spiral',
            self::Softcover => 'Jilid softcover',
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
