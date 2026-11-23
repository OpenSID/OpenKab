<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UserRegionAccess;
use App\Models\UserSectorAccess;
use App\Support\KodeWilayah;
use Illuminate\Support\Facades\DB;

/**
 * Hak akses wilayah dan sektor DataSpasial per pengguna (fitur 009, issue #1319/#1320).
 *
 * Aturan:
 * - Default BEBAS: semua pengguna melihat semua wilayah dan semua sektor.
 * - Pembatasan bersifat opsional. Tanpa baris di tabel, hak akses tidak dibatasi.
 * - Wilayah: satu pilihan bertingkat (kabupaten | kecamatan | desa) per pengguna.
 * - Sektor: satu atau lebih kode sektor per pengguna.
 *
 * Kode kabupaten diturunkan dari 4 digit pertama kode kecamatan; kode kecamatan dari 6 digit pertama kode desa.
 */
class CakupanPenggunaService
{
    public const LEVEL_WILAYAH = ['kabupaten', 'kecamatan', 'desa'];

    public function __construct(
        protected DataSpasialApiService $dataspasial,
    ) {}

    /**
     * Hak akses wilayah pengguna, atau null bila tidak dibatasi.
     *
     * @return array{level: string, kode: string}|null
     */
    public function wilayahAkses(int $userId): ?array
    {
        $baris = UserRegionAccess::where('user_id', $userId)->first();

        return $baris === null ? null : ['level' => (string) $baris->level, 'kode' => (string) $baris->region_code];
    }

    /**
     * Kode sektor yang boleh diakses pengguna, atau null bila tidak dibatasi.
     *
     * @return array<int, string>|null
     */
    public function sektorAkses(int $userId): ?array
    {
        $daftar = UserSectorAccess::where('user_id', $userId)->pluck('sektor_kode')->values()->all();

        return $daftar === [] ? null : $daftar;
    }

    /**
     * Ganti hak akses wilayah. `$level` dan `$kode` null → tidak dibatasi.
     */
    public function simpanWilayah(int $userId, ?string $level, ?string $kode): void
    {
        DB::transaction(function () use ($userId, $level, $kode) {
            UserRegionAccess::where('user_id', $userId)->delete();

            if ($level !== null && $kode !== null && $kode !== '') {
                UserRegionAccess::create(['user_id' => $userId, 'level' => $level, 'region_code' => $kode]);
            }
        });
    }

    /**
     * Ganti hak akses sektor. Daftar kosong → tidak dibatasi.
     *
     * @param  array<int, string>  $sektor
     */
    public function simpanSektor(int $userId, array $sektor): void
    {
        DB::transaction(function () use ($userId, $sektor) {
            UserSectorAccess::where('user_id', $userId)->delete();

            foreach (array_unique(array_filter($sektor)) as $kode) {
                UserSectorAccess::create(['user_id' => $userId, 'sektor_kode' => $kode]);
            }
        });
    }

    /**
     * Apakah pengguna boleh mengakses sektor ini. Tanpa pembatasan → selalu boleh.
     */
    public function bolehSektor(int $userId, ?string $kode): bool
    {
        $akses = $this->sektorAkses($userId);

        return $akses === null || ($kode !== null && in_array($kode, $akses, true));
    }

    /**
     * Saring baris menurut hak akses wilayah. Null (tanpa pembatasan) → baris tidak diubah.
     *
     * @param  array<int, array<string, mixed>>  $baris
     * @param  array{level: string, kode: string}|null  $wilayah
     * @return array<int, array<string, mixed>>
     */
    public function saringWilayah(array $baris, ?array $wilayah): array
    {
        if ($wilayah === null) {
            return $baris;
        }

        return array_values(array_filter($baris, fn (array $b) => $this->dalamWilayah(
            $wilayah,
            $b['kode_kecamatan'] ?? null,
            $b['kode_desa'] ?? null,
        )));
    }

    /**
     * Apakah sebuah kecamatan (dan desa, bila ada) berada dalam hak akses wilayah.
     */
    public function dalamWilayah(array $wilayah, ?string $kodeKecamatan, ?string $kodeDesa = null): bool
    {
        return match ($wilayah['level']) {
            KodeWilayah::KABUPATEN => KodeWilayah::kabupaten($kodeKecamatan) === $wilayah['kode'],
            KodeWilayah::KECAMATAN => $kodeKecamatan !== null && $kodeKecamatan === $wilayah['kode'],
            KodeWilayah::DESA => $kodeDesa !== null && $kodeDesa === $wilayah['kode'],
            default => false,
        };
    }

    /**
     * Penyaring baris (audit, monitoring, peta) sesuai hak akses. Null bila pengguna tidak dibatasi.
     *
     * Baris dicek dengan kunci `sektor_kode` (bila `$termasukSektor`), `kode_kecamatan`, dan `kode_desa`.
     */
    public function filterBaris(int $userId, bool $termasukSektor = true, bool $termasukWilayah = true): ?\Closure
    {
        $sektor = $termasukSektor ? $this->sektorAkses($userId) : null;
        $wilayah = $termasukWilayah ? $this->wilayahAkses($userId) : null;

        if ($sektor === null && $wilayah === null) {
            return null;
        }

        return function (array $baris) use ($sektor, $wilayah): bool {
            if ($sektor !== null && ! in_array((string) ($baris['sektor_kode'] ?? ''), $sektor, true)) {
                return false;
            }

            $desa = isset($baris['kode_desa']) && $baris['kode_desa'] !== '' ? (string) $baris['kode_desa'] : null;
            // Kode kecamatan diturunkan dari kode desa bila baris tidak memuatnya.
            $kecamatan = isset($baris['kode_kecamatan']) && $baris['kode_kecamatan'] !== ''
                ? (string) $baris['kode_kecamatan']
                : KodeWilayah::kecamatan($desa);

            return $wilayah === null || $this->dalamWilayah($wilayah, $kecamatan, $desa);
        };
    }

    /**
     * Apakah sebuah kecamatan termasuk dalam hak akses wilayah (untuk dropdown kecamatan).
     */
    public function kecamatanDalamWilayah(array $wilayah, string $kodeKecamatan): bool
    {
        return match ($wilayah['level']) {
            KodeWilayah::KABUPATEN => KodeWilayah::kabupaten($kodeKecamatan) === $wilayah['kode'],
            KodeWilayah::KECAMATAN => $kodeKecamatan === $wilayah['kode'],
            KodeWilayah::DESA => $kodeKecamatan === KodeWilayah::kecamatan($wilayah['kode']),
            default => false,
        };
    }

    /**
     * Kode wilayah untuk memaksa filter dasbor sesuai hak akses wilayah.
     *
     * @param  array{level: string, kode: string}  $wilayah
     * @return array{kode_kabupaten: string|null, kode_kecamatan: string|null, kode_desa: string|null}
     */
    public function filterDasbor(array $wilayah): array
    {
        $kode = $wilayah['kode'];
        $kab = KodeWilayah::kabupaten($kode);

        return match ($wilayah['level']) {
            KodeWilayah::KABUPATEN => ['kode_kabupaten' => $kab, 'kode_kecamatan' => null, 'kode_desa' => null],
            KodeWilayah::KECAMATAN => ['kode_kabupaten' => $kab, 'kode_kecamatan' => $kode, 'kode_desa' => null],
            KodeWilayah::DESA => ['kode_kabupaten' => $kab, 'kode_kecamatan' => KodeWilayah::kecamatan($kode), 'kode_desa' => $kode],
            default => ['kode_kabupaten' => null, 'kode_kecamatan' => null, 'kode_desa' => null],
        };
    }

    /**
     * Kode sektor dari id sektor API (untuk dicocokkan dengan hak akses).
     */
    public function kodeSektorDariId(int $sektorId): ?string
    {
        $hasil = $this->dataspasial->listSektor();
        foreach ($hasil->isData() ? $hasil->data : [] as $s) {
            if ((int) ($s['id'] ?? 0) === $sektorId) {
                return $s['attributes']['kode'] ?? null;
            }
        }

        return null;
    }
}
