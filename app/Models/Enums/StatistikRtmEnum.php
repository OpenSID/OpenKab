<?php

namespace App\Models\Enums;

use BenSampo\Enum\Enum;

class StatistikRtmEnum extends Enum
{
    public const BDT = [
        'key' => 'bdt',
        'slug' => 'bdt',
        'label' => 'BDT',
    ];

    public static $data = [
        self::BDT,
    ];

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return collect(self::$data)->pluck('label', 'slug')->toArray();
    }

    /**
     * Get slug from key.
     */
    public static function slugFromKey(mixed $key): ?string
    {
        $item = collect(self::$data)->firstWhere('key', $key);

        return $item ? $item['slug'] : null;
    }

    /**
     * Get all key label.
     */
    public static function allKeyLabel(): array
    {
        return collect(self::$data)->pluck('label', 'key')->toArray();
    }

    /**
     * Get key form slug.
     */
    public static function keyFromSlug(mixed $slug): ?string
    {
        $item = collect(self::$data)->firstWhere('slug', $slug);

        return $item ? $item['key'] : null;
    }
}
