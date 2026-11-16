<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Tipe isian pada skema kabupaten (FR-010).
 *
 * Nilai ini mengikuti enum `tipe` pada entitas `ds_isian` di kontrak
 * `openapi-fondasi.yaml` (`components.schemas.Isian`). Nilai BARU hanya boleh
 * ditambah lewat perubahan kontrak API-01, bukan diam-diam di sini.
 *
 * @method static static Angka()
 * @method static static Teks()
 * @method static static Pilihan()
 * @method static static YaTidak()
 * @method static static Tanggal()
 */
final class TipeIsian extends Enum
{
    public const Angka = 'angka';

    public const Teks = 'teks';

    public const Pilihan = 'pilihan';

    public const YaTidak = 'ya_tidak';

    public const Tanggal = 'tanggal';

    /**
     * Kolom "opsi" hanya berlaku untuk tipe `pilihan` (FR-010).
     *
     * Method helper pada enum ini yang dipakai view agar keputusan
     * "opsi berlaku atau tidak" TIDAK diulang di setiap Blade.
     */
    public function memakaiPilihan(): bool
    {
        return $this->value === self::Pilihan;
    }

    /**
     * Satuan relevan hanya untuk tipe yang menyimpan besaran (FR-010).
     */
    public function memakaiSatuan(): bool
    {
        return $this->value === self::Angka;
    }
}
