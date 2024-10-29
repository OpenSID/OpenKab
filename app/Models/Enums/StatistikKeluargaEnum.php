<?php

namespace App\Models\Enums;

use BenSampo\Enum\Enum;


class StatistikKeluargaEnum extends Enum
{
    public const KELAS_SOSIAL = [
        'key'   => 'kelas_sosial',
        'slug'  => 'kelas-sosial',
        'label' => 'Kelas Sosial',
        'url'   => 'statistik/kelas-sosial',
    ];

    public static $data = [
        self::KELAS_SOSIAL,
    ];

    /**
     * Override method all()
     */
    public static function all(): array
    {
        return collect(self::$data)->pluck('label', 'slug')->toArray();
    }

    /**
     * Get slug from key
     */
    public static function slugFromKey(mixed $key): ?string
    {
        $item = collect(self::$data)->firstWhere('key', $key);

        return $item ? $item['slug'] : null;
    }

    /**
     * Get all key label
     */
    public static function allKeyLabel(): array
    {
        return collect(self::$data)->pluck('label', 'key')->toArray();
    }

    /**
     * Get key form slug
     */
    public static function keyFromSlug(mixed $slug): ?string
    {
        $item = collect(self::$data)->firstWhere('slug', $slug);

        return $item ? $item['key'] : null;
    }
}
