<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Sumber penulisan entri DataSpasial (API-08 / #474).
 *
 * Nilai mengikuti `ds_audit.sumber_tulis` di sisi API Satu Data:
 * - `openkab` → entri datang dari OpenKab (impor, terbitan skema, dsb.)
 * - `opensid` → entri datang dari aplikasi OpenSID desa
 *
 * @method static static Openkab()
 * @method static static Opensid()
 */
final class SumberEntri extends Enum
{
    public const Openkab = 'openkab';

    public const Opensid = 'opensid';

    /**
     * Label yang ditampilkan pada badge sumber.
     */
    public function label(): string
    {
        return match ($this->value) {
            self::Openkab => 'OpenKab',
            self::Opensid => 'OpenSID',
            default => '—',
        };
    }

    /**
     * Kelas CSS badge (AdminLTE 4) — warna berbeda per sumber.
     */
    public function badgeClass(): string
    {
        return match ($this->value) {
            self::Openkab => 'badge-primary',
            self::Opensid => 'badge-info',
            default => 'badge-secondary',
        };
    }

    /**
     * Semua nilai yang sah (untuk filter).
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [self::Openkab, self::Opensid];
    }
}
