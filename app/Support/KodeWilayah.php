<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Helper kode wilayah Indonesia (standar Kemendagri) untuk DataSpasial.
 *
 * Susunan kode, dari kiri ke kanan:
 *   kabupaten  = 4 digit   (contoh: 5102 = Tabanan)
 *   kecamatan  = 6 digit   (contoh: 510210 = kecamatan di dalam 5102)
 *   desa       = 10 digit  (contoh: 5102102009 = desa di dalam 510210)
 *
 * Tabel `user_region_access` menyimpan kode sesuai level (`region_code`).
 * Kode level di atasnya selalu diturunkan dari awalan kode, bukan disimpan terpisah.
 */
final class KodeWilayah
{
    public const KABUPATEN = 'kabupaten';

    public const KECAMATAN = 'kecamatan';

    public const DESA = 'desa';

    /** Panjang kode per level. */
    public const PANJANG = [
        self::KABUPATEN => 4,
        self::KECAMATAN => 6,
        self::DESA => 10,
    ];

    /** Level yang valid untuk pembatasan wilayah. */
    public const LEVEL = [self::KABUPATEN, self::KECAMATAN, self::DESA];

    /**
     * Kode kabupaten dari kode apa pun yang lebih rinci (kecamatan atau desa). Null bila terlalu pendek.
     */
    public static function kabupaten(?string $kode): ?string
    {
        return self::awalan($kode, self::PANJANG[self::KABUPATEN]);
    }

    /**
     * Kode kecamatan dari kode desa (atau kecamatan itu sendiri). Null bila terlalu pendek.
     */
    public static function kecamatan(?string $kode): ?string
    {
        return self::awalan($kode, self::PANJANG[self::KECAMATAN]);
    }

    /**
     * Level sebuah kode berdasarkan panjangnya, atau null bila tidak sesuai format level mana pun.
     */
    public static function levelDari(?string $kode): ?string
    {
        foreach (self::LEVEL as $level) {
            if (self::sesuaiLevel($level, $kode)) {
                return $level;
            }
        }

        return null;
    }

    /**
     * Apakah kode berisi tepat digit sesuai panjang level (kabupaten 4, kecamatan 6, desa 10).
     */
    public static function sesuaiLevel(string $level, ?string $kode): bool
    {
        $panjang = self::PANJANG[$level] ?? null;

        return $panjang !== null && $kode !== null && preg_match('/^[0-9]{'.$panjang.'}$/', $kode) === 1;
    }

    /**
     * Kode induk pada level tertentu dari sebuah kode. Mengembalikan null bila kode tidak cukup panjang.
     */
    public static function ke(?string $kode, string $level): ?string
    {
        return match ($level) {
            self::KABUPATEN => self::kabupaten($kode),
            self::KECAMATAN => self::kecamatan($kode),
            self::DESA => self::sesuaiLevel(self::DESA, $kode) ? $kode : null,
            default => null,
        };
    }

    private static function awalan(?string $kode, int $panjang): ?string
    {
        if ($kode === null || strlen($kode) < $panjang) {
            return null;
        }

        return substr($kode, 0, $panjang);
    }
}
