<?php

namespace App\Models\Enums;

use BenSampo\Enum\Enum;

class StatistikJenisBantuanEnum extends Enum
{
    public const PENDUDUK = [
        'key'   => 'bantuan_penduduk',
        'slug'  => 'bantuan-penduduk',
        'label' => 'Bantuan Penduduk',
    ];
    public const KELUARGA = [
        'key'   => 'bantuan_keluarga',
        'slug'  => 'bantuan-keluarga',
        'label' => 'Bantuan Keluarga',
    ];

    public static $data = [
        self::PENDUDUK,
        self::KELUARGA,
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
