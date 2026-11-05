<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Status adopsi skema per desa (KAB-03 / FR-061).
 *
 * ⚠️ **INI BUKAN state yang disimpan di API.** Enum ini memodelkan **label
 * bahasa pengguna** untuk nilai `status` yang dikembalikan API Satu Data,
 * sehingga seluruh tempat yang menampilkan status memakai satu sumber
 * kebenaran (research.md R13).
 *
 * ⚠️ **VERIFIKASI LANGSUNG (2026-10-16)** — API hanya punya **dua** nilai:
 * `memakai_skema` dan `belum_ada_data`. Nilai "tertinggal" yang dipakai pada
 * tangkapan layar acuan **tidak ada di kontrak**, sehingga status **Tertinggal**
 * diturunkan dari perbandingan nomor versi terpakai dengan nomor versi terbit
 * terbaru sektor tersebut (lihat `AdopsiDesaService::barisSektor()`).
 *
 * Kontrak `openapi-fondasi.yaml` menyebut `terbaru`/`tertinggal`/`belum` pada
 * field `status_adopsi` — itu **draft**, bukan implementasi. Lihat
 * `specs/005-penerbitan-adopsi-skema/contracts/penerbitan-adopsi-api.md` §5.2.
 *
 * @method static static Terpusat()
 * @method static static Tertinggal()
 * @method static static MenungguAdopsi()
 * @method static static TidakDiketahui()
 */
final class StatusAdopsiDesa extends Enum
{
    /**
     * Desa memakai versi terbit terbaru sektor tersebut.
     */
    public const Terpusat = 'terpusat';

    /**
     * Desa memakai versi lama → **diturunkan** dari nomor versi (bukan enum API).
     */
    public const Tertinggal = 'tertinggal';

    /**
     * Desa belum pernah menarik skema (`belum_ada_data`).
     */
    public const MenungguAdopsi = 'menunggu_adopsi';

    /**
     * Nilai status yang tidak dikenali atau kosong → tampil "—" (FR-071).
     */
    public const TidakDiketahui = 'tidak_diketahui';

    /**
     * Label yang ditampilkan ke admin.
     */
    public function label(): string
    {
        return match ($this->value) {
            self::Terpusat => 'Terpusat',
            self::Tertinggal => 'Tertinggal',
            self::MenungguAdopsi => 'Menunggu adopsi',
            default => '—',
        };
    }

    /**
     * Warna lencana Bootstrap (kelas CSS).
     */
    public function warnaLencana(): string
    {
        return match ($this->value) {
            self::Terpusat => 'badge-success',
            self::Tertinggal => 'badge-warning',
            self::MenungguAdopsi => 'badge-info',
            default => 'badge-light',
        };
    }

    /**
     * Status ini punya sumber data sehingga **boleh** dipakai sebagai saringan.
     *
     * Status `tidak_diketahui` tidak boleh disaring: hasil saringan atas nilai
     * yang tidak ada mapanya akan menyesatkan (FR-061/FR-063).
     */
    public function bolehDisaring(): bool
    {
        return $this->value !== self::TidakDiketahui;
    }

    /**
     * Status ini menjadi penanda desa tertinggal (FR-062).
     */
    public function ditandai(): bool
    {
        return $this->value === self::Tertinggal;
    }

    /**
     * Peta nilai `status` dari API → status UI.
     *
     * @param  string  $statusApi  nilai `attributes.status` dari API
     * @param  int|null  $nomorVersiTerpakai  `nomor_versi_skema` (null = belum adopsi)
     * @param  int|null  $nomorVersiTerbaru  nomor versi terbit terbaru sektor (null = belum diketahui)
     */
    public static function dariApi(?string $statusApi, ?int $nomorVersiTerpakai, ?int $nomorVersiTerbaru): self
    {
        if ($statusApi === 'belum_ada_data' || $nomorVersiTerpakai === null) {
            return self::fromValue(self::MenungguAdopsi);
        }

        if ($statusApi !== 'memakai_skema') {
            return self::fromValue(self::TidakDiketahui);
        }

        // ⚠️ FR-061 — versi yang LEBIH BARU dari versi terbit terbaru diperlakukan
        // sebagai data anomali: status Terpusat, **tidak boleh** diklaim tertinggal.
        if ($nomorVersiTerbaru === null || $nomorVersiTerpakai >= $nomorVersiTerbaru) {
            return self::fromValue(self::Terpusat);
        }

        return self::fromValue(self::Tertinggal);
    }

    /**
     * Daftar status yang boleh dipakai sebagai pilihan saringan.
     *
     * @return array<int, self>
     */
    public static function pilihanSaringan(): array
    {
        return [
            self::fromValue(self::Terpusat),
            self::fromValue(self::Tertinggal),
            self::fromValue(self::MenungguAdopsi),
        ];
    }
}
