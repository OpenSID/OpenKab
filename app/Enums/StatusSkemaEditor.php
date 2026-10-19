<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Keadaan editor skema pada satu sektor di sisi UI.
 *
 * INI BUKAN state yang disimpan di API — enum ini hanya memodelkan "apa yang
 * boleh diinteraksi" pada satu render halaman, sehingga aturan tombol simpan
 * dan tombol tambah isian tidak tersebar di setiap Blade.
 *
 * Status sebenarnya (`draf` / `terbit`) datang dari API; `Kosong` dan
 * `BelumTersedia` diturunkan dari hasil `DataSpasialResult` (FR-005/FR-006).
 *
 * @method static static Kosong()
 * @method static static Draf()
 * @method static static Terbit()
 * @method static static BelumTersedia()
 */
final class StatusSkemaEditor extends Enum
{
    /**
     * Sektor belum punya skema sama sekali (FR-008b).
     */
    public const Kosong = 'kosong';

    /**
     * Sedang ada draf berjalan — satu-satunya keadaan yang dapat diubah (FR-008b).
     */
    public const Draf = 'draf';

    /**
     * Hanya ada versi terbit — ditampilkan baca-saja (FR-008a).
     */
    public const Terbit = 'terbit';

    /**
     * Sumber data tidak dapat dihubungi; seluruh tombol nonaktif (FR-005).
     */
    public const BelumTersedia = 'belum_tersedia';

    /**
     * Tombol "Simpan draf" hanya aktif pada keadaan draf (FR-008b).
     */
    public function bolehSimpan(): bool
    {
        return $this->value === self::Draf;
    }

    /**
     * Tombol tambah/hapus/urut isian hanya aktif pada keadaan draf (FR-008a).
     */
    public function bolehUbah(): bool
    {
        return $this->value === self::Draf;
    }

    /**
     * Menyusun keadaan editor dari skema yang dipilih.
     *
     * @param  array{status: string, jumlah_skema: int}  $skema
     */
    public static function dari(array $skema): self
    {
        return match ($skema['status'] ?? '') {
            self::Terbit => self::fromValue(self::Terbit),
            self::Draf => self::fromValue(self::Draf),
            default => self::fromValue(($skema['jumlah_skema'] ?? 0) > 0 ? self::Terbit : self::Kosong),
        };
    }
}
