<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Daftar data hasil impor (fitur 009, issue #1319).
 *
 * Membaca nilai isian dari API Satu Data, lalu:
 * 1. mengatribusikan setiap baris ke desa lewat peta `fasilitas_id → wilayah`
 *    (API belum memuat `wilayah` pada `nilai`; lihat contracts/permintaan-tim-api.md REQ-01);
 * 2. membuang baris yang bukan `sumber_data = impor`;
 * 3. menyaring ulang di sisi OpenKab karena API saat ini mengabaikan
 *    `sumber_data` dan `isian_id` (REQ-02, REQ-03 di permintaan tim API).
 *
 * Semua pemanggilan lewat DataSpasialApiService (token server-side, cache, header audit).
 */
class DataSpasialImporListService
{
    /** Batas ukuran halaman di sisi API (`page[size]` maks 100). */
    private const UKURAN_HALAMAN_API = 100;

    /** Pengaman agar iterasi halaman tidak berjalan tanpa batas. */
    private const MAKS_HALAMAN = 200;

    /** Diatur true bila salah satu halaman API gagal (unavailable), agar UI bisa menampilkan pesan generik. */
    private bool $adaGalat = false;

    public function __construct(
        protected DataSpasialApiService $dataspasial,
    ) {}

    public function adaGalat(): bool
    {
        return $this->adaGalat;
    }

    /**
     * Semua baris impor untuk satu sektor, opsional periode dan kecamatan.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ambilBaris(int $sektorId, ?string $periode, ?string $kodeKecamatan, ?int $skemaVersiId = null): array
    {
        $filterWilayah = array_filter([
            'sektor_id' => $sektorId,
            'periode' => $periode,
            'kode_kecamatan' => $kodeKecamatan,
        ], fn ($v) => $v !== null && $v !== '');

        // Endpoint fasilitas tidak menerima periode dan tidak difilter versi (agar desa tidak hilang dari peta).
        $peta = $this->petaFasilitasKeWilayah(array_diff_key($filterWilayah, ['periode' => true]));

        // Versi dikirim ke endpoint nilai; hasilnya tetap disaring ulang per skema_versi_id di saring().
        $permintaanNilai = $filterWilayah + ['sumber_data' => 'impor'];
        if ($skemaVersiId !== null) {
            $permintaanNilai['skema_versi_id'] = $skemaVersiId;
        }

        $baris = [];
        foreach ($this->semuaHalaman('nilai', $permintaanNilai) as $item) {
            $atribut = $item['attributes'] ?? [];
            $barisNormal = $this->normalisasi($atribut, $peta);
            if ($barisNormal !== null) {
                $baris[] = $barisNormal;
            }
        }

        return $baris;
    }

    /**
     * Saring baris menurut isian, desa, dan teks bebas.
     *
     * @param  array<int, array<string, mixed>>  $baris
     * @param  array{isian_kode?: array<int, string>, kode_desa?: string|null, q?: string|null}  $filter
     * @return array<int, array<string, mixed>>
     */
    public function saring(array $baris, array $filter): array
    {
        $isian = array_filter($filter['isian_kode'] ?? []);
        $desa = $filter['kode_desa'] ?? null;
        $q = isset($filter['q']) ? mb_strtolower(trim((string) $filter['q'])) : '';
        // Tahun disaring dari awalan periode (YYYY-MM); API hanya menerima periode bulan.
        $tahun = isset($filter['tahun']) && $filter['tahun'] !== '' ? (string) $filter['tahun'] : null;
        // Versi skema disaring dari skema_versi_id (= id riwayat versi) yang terpasang pada setiap baris.
        $versi = isset($filter['versi']) && $filter['versi'] !== '' ? (int) $filter['versi'] : null;

        return array_values(array_filter($baris, function (array $b) use ($isian, $desa, $q, $tahun, $versi) {
            if ($tahun !== null && substr((string) ($b['periode'] ?? ''), 0, 4) !== $tahun) {
                return false;
            }
            if ($versi !== null && (int) ($b['skema_versi_id'] ?? 0) !== $versi) {
                return false;
            }
            if ($isian !== [] && ! in_array($b['isian_kode'], $isian, true)) {
                return false;
            }
            if ($desa !== null && $desa !== '' && $b['kode_desa'] !== $desa) {
                return false;
            }
            if ($q !== '') {
                $haystack = mb_strtolower(($b['nama_desa'] ?? '').' '.($b['isian_label'] ?? ''));

                return str_contains($haystack, $q);
            }

            return true;
        }));
    }

    /**
     * Angka ringkasan dari baris yang sudah disaring.
     *
     * @param  array<int, array<string, mixed>>  $baris
     * @return array{total_baris: int, total_desa: int, total_nilai_angka: int}
     */
    public function ringkasan(array $baris): array
    {
        $desa = array_unique(array_filter(array_column($baris, 'kode_desa')));

        return [
            'total_baris' => count($baris),
            'total_desa' => count($desa),
            'total_nilai_angka' => count(array_filter(
                $baris,
                fn (array $b) => $b['isian_tipe'] === 'angka' && $b['nilai'] !== null,
            )),
        ];
    }

    /**
     * Peta `fasilitas_id → {kode_desa, nama_desa, kode_kecamatan}` dari endpoint fasilitas.
     *
     * @param  array<string, mixed>  $filter
     * @return array<int|string, array{kode_desa: string, nama_desa: string, kode_kecamatan: string}>
     */
    private function petaFasilitasKeWilayah(array $filter): array
    {
        $peta = [];
        foreach ($this->semuaHalaman('fasilitas', $filter) as $item) {
            $wilayah = $item['attributes']['wilayah'] ?? null;
            if (! is_array($wilayah)) {
                continue;
            }
            $peta[$item['id'] ?? ''] = [
                'kode_desa' => (string) ($wilayah['kode_desa'] ?? ''),
                'nama_desa' => (string) ($wilayah['nama_desa'] ?? ''),
                'kode_kecamatan' => (string) ($wilayah['kode_kecamatan'] ?? ''),
                'nama_fasilitas' => isset($item['attributes']['nama']) ? (string) $item['attributes']['nama'] : null,
                'kode_fasilitas' => isset($item['attributes']['kode_fasilitas']) ? (string) $item['attributes']['kode_fasilitas'] : null,
            ];
        }

        return $peta;
    }

    /**
     * Normalisasi satu item nilai. Mengembalikan null bila baris dibuang.
     *
     * @param  array<string, mixed>  $atribut
     * @param  array<int|string, array<string, string>>  $peta
     * @return array<string, mixed>|null
     */
    private function normalisasi(array $atribut, array $peta): ?array
    {
        if (($atribut['sumber_data'] ?? null) !== 'impor') {
            return null;
        }

        $isianKode = $atribut['isian_kode'] ?? null;
        if ($isianKode === null || $isianKode === '') {
            Log::warning('DataSpasial: baris impor tanpa isian_kode dilewati', [
                'fasilitas_id' => $atribut['fasilitas_id'] ?? null,
            ]);

            return null;
        }

        $fasilitasId = $atribut['fasilitas_id'] ?? null;
        $wilayah = $atribut['wilayah'] ?? null;

        if (! is_array($wilayah)) {
            $wilayah = $peta[$fasilitasId] ?? null;
            if ($wilayah === null) {
                Log::warning('DataSpasial: fasilitas tidak ditemukan di peta wilayah', [
                    'fasilitas_id' => $fasilitasId,
                ]);
            }
        }

        $nilai = $atribut['nilai'] ?? null;
        $tipe = $atribut['isian_tipe'] ?? null;
        if ($tipe === 'angka' && $nilai !== null && ! is_numeric($nilai)) {
            $nilai = null;
        }

        // Nama dan jenis fasilitas dari endpoint fasilitas. Jenis = kode fasilitas (API tidak punya label jenis).
        $fasilitas = $peta[$fasilitasId] ?? [];

        return [
            'skema_versi_id' => isset($atribut['skema_versi']['id']) ? (int) $atribut['skema_versi']['id'] : null,
            'nomor_versi' => isset($atribut['skema_versi']['nomor_versi']) ? (int) $atribut['skema_versi']['nomor_versi'] : null,
            'nama_fasilitas' => $fasilitas['nama_fasilitas'] ?? null,
            'jenis_fasilitas' => $fasilitas['kode_fasilitas'] ?? null,
            'fasilitas_id' => $fasilitasId,
            'kode_desa' => $wilayah['kode_desa'] ?? null,
            'nama_desa' => $wilayah['nama_desa'] ?? null,
            'kode_kecamatan' => $wilayah['kode_kecamatan'] ?? null,
            'isian_kode' => $isianKode,
            'isian_label' => $atribut['isian_label'] ?? $isianKode,
            'isian_tipe' => $tipe,
            'isian_satuan' => $atribut['isian_satuan'] ?? null,
            'nilai' => $nilai,
            'periode' => $atribut['periode'] ?? null,
            'waktu_input' => $atribut['waktu_input'] ?? null,
            'sumber_data' => 'impor',
            'sektor' => $atribut['sektor']['nama'] ?? null,
        ];
    }

    /**
     * Ambil seluruh item dari satu koleksi API dengan iterasi halaman.
     *
     * @param  array<string, mixed>  $filter
     * @return \Generator<int, array<string, mixed>>
     */
    private function semuaHalaman(string $koleksi, array $filter): \Generator
    {
        $nomor = 1;
        do {
            $query = $filter + [
                'page[number]' => $nomor,
                'page[size]' => self::UKURAN_HALAMAN_API,
            ];

            $hasil = $koleksi === 'nilai'
                ? $this->dataspasial->listNilai($query)
                : $this->dataspasial->listFasilitas($query);

            if (! $hasil->isData()) {
                if ($hasil->isUnavailable()) {
                    $this->adaGalat = true;
                }

                return;
            }

            foreach ($hasil->data as $item) {
                yield $item;
            }

            $totalHalaman = (int) ($hasil->pagination()['total_pages'] ?? 1);
            $nomor++;
        } while ($nomor <= $totalHalaman && $nomor <= self::MAKS_HALAMAN);
    }
}
