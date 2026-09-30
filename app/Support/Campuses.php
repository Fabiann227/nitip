<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Campus master data lives in config/nitip.php (no table) to keep the schema small.
 */
final class Campuses
{
    /**
     * @return array<string, array{name: string, city?: string, domains?: list<string>, locations?: list<string>}>
     */
    public static function all(): array
    {
        return config('nitip.campuses', []);
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(self::all());
    }

    /**
     * @return array{name: string, city?: string, domains?: list<string>, locations?: list<string>}|null
     */
    public static function find(?string $code): ?array
    {
        if ($code === null) {
            return null;
        }

        return self::all()[$code] ?? null;
    }

    public static function name(?string $code): string
    {
        return self::find($code)['name'] ?? ($code ?: '-');
    }

    /**
     * @return list<string>
     */
    public static function locations(?string $code): array
    {
        return self::find($code)['locations'] ?? [];
    }

    /**
     * Accepts campus emails: registered campus domains OR the generic .ac.id suffix.
     */
    public static function allowsEmail(?string $code, string $email): bool
    {
        $domain = Str::lower(Str::after($email, '@'));

        foreach (config('nitip.campus_email_suffixes', []) as $suffix) {
            if (Str::endsWith($domain, Str::lower($suffix))) {
                return true;
            }
        }

        foreach (self::find($code)['domains'] ?? [] as $allowed) {
            $allowed = Str::lower(trim($allowed));

            if ($allowed !== '' && ($domain === $allowed || Str::endsWith($domain, '.'.$allowed))) {
                return true;
            }
        }

        return false;
    }

    public static function domainsLabel(?string $code): string
    {
        return implode(', ', self::find($code)['domains'] ?? []);
    }
}
