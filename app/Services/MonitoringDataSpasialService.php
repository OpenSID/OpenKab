<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pipeline monitoring pengisian & kualitas data desa (KAB-07).
 *
 * Satu-satunya sumber angka untuk kartu, tabel, dan ekspor (D-001 — jaminan
 * SC-002/SC-003/SC-005 selisih 0). Sumber data (kontrak §8 hasil K-verify):
 *   - adopsi (#468)      → cakupan semua desa, versi terpakai, status, `terakhir_ada_data`
 *   - riwayat + skema    → versi terbit terbaru + isian aktif (denominator, tipe angka)
 *   - ringkasan (#469)   → keberadaan desa ber-data + fallback `total_isian`
 *   - nilai (#469)       → rincian per desa (`?kode_desa=`): nilai per isian,
 *                          `sumber_data` (manual/desa/impor/pemetaan), kelengkapan
 *   - statistik-web      → (digantikan) daftar desa per kecamatan lewat
 *                          `ConfigApiService::desa()` (`/api/v1/config/desa`
 *                          dengan `filter[kode_kecamatan]`, di-cache service)
 */
class MonitoringDataSpasialService
{
    public const PER_HALAMAN = 25;

    public const BATAS_EKSPOR = 5000;

    /**
     * Nilai `kode_kecamatan` untuk seluruh kecamatan (FR-004).
     */
    public const SEMUA_KECAMATAN = 'SEMUA';

    public function __construct(
        public readonly DataSpasialApiService $dataspasial,
        private readonly AdopsiDesaService $adopsi,
        private readonly ConfigApiService $configApi,
    ) {}

    // -----------------------------------------------------------------------
    // Filter
    // -----------------------------------------------------------------------

    /**
     * Nilai filter dari query string (validasi wajib dilakukan controller).
     *
     * `kode_kecamatan` OPSIONAL — kosong = "Semua Kecamatan". Tanpa filter
     * status maupun kata kunci nama desa (dihapus 2026-10-21).
     *
     * @param  array<string, mixed>  $query
     * @return array{sektor: string, kode_kecamatan: string, periode: string|null, page: int, per_page: int}
     */
    public function filter(array $query): array
    {
        $kecamatan = trim((string) ($query['kode_kecamatan'] ?? ''));

        return [
            'sektor' => trim((string) ($query['sektor'] ?? '')),
            'kode_kecamatan' => $kecamatan === '' ? self::SEMUA_KECAMATAN : $kecamatan,
            'periode' => ($query['periode'] ?? null) !== null && trim((string) $query['periode']) !== ''
                ? trim((string) $query['periode'])
                : null,
            'page' => max(1, (int) ($query['page'] ?? 1)),
            'per_page' => max(1, min(200, (int) ($query['per_page'] ?? self::PER_HALAMAN))),
        ];
    }

    /**
     * Bangun seluruh monitoring (kartu + tabel + meta) untuk filter valid.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function buildMonitoring(array $query): array
    {
        $filter = $this->filter($query);

        $ambil = $this->adopsi->ambilSemua();

        if (! $ambil['tersedia']) {
            return $this->payloadTidakTersedia($filter);
        }

        $versiTerbit = $this->adopsi->versiTerbaruSektor($filter['sektor']);
        $metaIsian = $this->isianAktif($filter['sektor'], $versiTerbit);
        $sektorId = $this->sektorId($filter['sektor']);

        $barisAdopsi = $this->barisAdopsiSektor($ambil['baris'], $sektorId);
        $baris = $this->susunBarisDasar($barisAdopsi, $filter['kode_kecamatan']);

        if ($baris === []) {
            return $this->payloadKosong($filter, [], 'Tidak ada desa pada lingkup ini.');
        }

        // Periode: default terbaru berdata dari `terakhir_ada_data` adopsi (K-verify §8.5).
        $periods = $this->periodeBerdata($ambil['baris']);
        $periode = $filter['periode'] ?? ($periods[0] ?? date('Y-m'));

        if ($filter['periode'] !== null && $periods !== [] && ! in_array($filter['periode'], $periods, true)) {
            // Kombinasi filter tanpa data (FR-061) — bukan "semua belum mengisi".
            return $this->payloadKosong($filter, $periods, null, $periode);
        }

        // Ringkasan per desa: keberadaan ber-data + fallback total_isian.
        $ringkasanQuery = array_filter([
            'kode_kecamatan' => $filter['kode_kecamatan'] === self::SEMUA_KECAMATAN ? null : $filter['kode_kecamatan'],
            'sektor' => $filter['sektor'],
            'periode' => $periode,
            'group_by' => 'desa',
        ], static fn ($v) => $v !== null);

        $ringkasan = $this->dataspasial->ringkasan($ringkasanQuery);
        
        $totalIsian = [];
        $ringkasanTersedia = ! $ringkasan->isUnavailable();

        if ($ringkasanTersedia) {
            foreach ($ringkasan->data as $item) {
                $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : $item;
                $kode = (string) ($atribut['wilayah_kode'] ?? '');

                if ($kode !== '') {
                    $totalIsian[$kode] = (int) ($atribut['total_isian'] ?? 0);
                }
            }
        }

        // Rincian per desa (`nilai?kode_desa=`) — hanya desa yang ber-data
        // (ringkasan) atau seluruh desa bila ringkasan tidak tersedia.
        $target = $ringkasanTersedia
            ? array_values(array_intersect(array_keys($baris), array_keys($totalIsian)))
            : array_keys($baris);

        $detail = $this->detailPerDesa($target, $filter['sektor'], $periode);

        $barisLengkap = [];
        $namaKecamatan = $this->petaNamaKecamatan();
        
        foreach ($baris as $kodeDesa => $dasar) {
            $kodeDesa = (string) $kodeDesa; // key array numerik bisa berupa int
            $hasil = $detail[$kodeDesa] ?? null; // null = rincian tidak terbaca
            $berdata = array_key_exists($kodeDesa, $totalIsian)
                || ($hasil !== null && $hasil !== []);

            $nilaiRows = $hasil ?? [];
            $kodeKecamatanDesa = substr($kodeDesa, 0, 6);

            $barisLengkap[$kodeDesa] = array_merge($dasar, [
                'kode_kecamatan' => $kodeKecamatanDesa,
                'nama_kecamatan' => $namaKecamatan[$kodeKecamatanDesa] ?? $kodeKecamatanDesa,
            ], $this->hitungBaris(
                $dasar,
                $versiTerbit,
                $berdata,
                $nilaiRows,
                $hasil === null
                    ? ($totalIsian[$kodeDesa] ?? ($ringkasanTersedia ? 0 : null))
                    : null,
                $hasil === null && ! $ringkasanTersedia,
                $metaIsian,
                $periode
            ));
        }

        // Tanpa saringan status/nama (dihapus 2026-10-21). Urutan bawaan
        // FR-021: perlu tindakan dulu, lalu nama menaik.
        $terfilter = array_values($barisLengkap);

        usort($terfilter, static function (array $a, array $b): int {
            if ($a['perlu_tindakan'] !== $b['perlu_tindakan']) {
                return $b['perlu_tindakan'] <=> $a['perlu_tindakan'];
            }

            return strcasecmp((string) $a['nama_desa'], (string) $b['nama_desa']);
        });

        // Kartu atas SELURUH hasil (FR-010). "Perlu diperiksa" kini = desa
        // dengan versi tertinggal (tanda data dihapus 2026-10-21).
        $sudah = count(array_filter($terfilter, static fn (array $b): bool => $b['status_pengisian'] === 'sudah_mengisi'));
        $belum = count(array_filter($terfilter, static fn (array $b): bool => $b['status_pengisian'] === 'belum_pernah_mengirim'));
        $perlu = count(array_filter($terfilter, static fn (array $b): bool => $b['perlu_tindakan']));

        $kartu = [
            'sudah_mengisi' => $sudah,
            'belum_pernah_mengirim' => $belum,
            'perlu_diperiksa' => $perlu,
            'total_desa' => count($terfilter),
            'sudah_mengisi_label' => $sudah.'/'.count($terfilter),
        ];

        $total = count($terfilter);
        $totalHalaman = (int) max(1, ceil($total / $filter['per_page']));
        $halaman = max(1, min($filter['page'], $totalHalaman));

        return [
            'meta' => [
                'unavailable' => false,
                'empty' => $total === 0,
                'message' => $total === 0 ? 'Tidak ada data untuk pilihan ini.' : null,
                'validation' => null,
                'sektor' => $filter['sektor'],
                'kode_kecamatan' => $filter['kode_kecamatan'],
                'periode' => $periode,
                'periods' => $periods,
                'page' => $halaman,
                'per_page' => $filter['per_page'],
                'total' => $total,
                'total_pages' => $totalHalaman,
            ],
            'kartu' => $kartu,
            'tabel' => [
                'columns' => ['desa', 'kecamatan', 'kelengkapan', 'kualitas', 'status'],
                'rows' => array_values(array_slice($terfilter, ($halaman - 1) * $filter['per_page'], $filter['per_page'])),
            ],
            // Untuk ekspor: seluruh baris tersaring (SC-005).
            'semua_baris' => $terfilter,
        ];
    }

    /**
     * Ekspor CSV laporan bulanan (FR-051/FR-052, D-005).
     *
     * @param  array<string, mixed>  $query
     * @return array{response: StreamedResponse, nama: string, dipangkas: bool, total: int}|null null bila hasil kosong
     */
    public function eksporCsv(array $query): ?array
    {
        $built = $this->buildMonitoring($query);
        $filter = $this->filter($query);

        if (($built['meta']['unavailable'] ?? true) === true) {
            return null;
        }

        $semua = $built['semua_baris'];

        if ($semua === []) {
            return null;
        }

        $dipangkas = count($semua) > self::BATAS_EKSPOR;
        $baris = array_slice($semua, 0, self::BATAS_EKSPOR);
        $periode = (string) $built['meta']['periode'];
        $dibuat = now()->format('Y-m-d H:i:s');
        $kriteria = sprintf(
            'sektor=%s; kecamatan=%s; periode=%s',
            $filter['sektor'],
            $filter['kode_kecamatan'],
            $periode
        );

        $nama = sprintf('monitoring-laporan-%s-%s.csv', $periode, now()->format('Ymd-His'));

        $response = response()->streamDownload(function () use ($baris, $periode, $dibuat, $kriteria, $dipangkas) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['# Periode laporan', $periode], ',', '"', '\\');
            fputcsv($out, ['# Dibuat', $dibuat], ',', '"', '\\');
            fputcsv($out, ['# Filter', $kriteria], ',', '"', '\\');
            fputcsv($out, ['# Sumber', 'Monitoring OpenKab KAB-07'], ',', '"', '\\');

            if ($dipangkas) {
                fputcsv($out, ['# CATATAN', 'dipangkas pada batas '.self::BATAS_EKSPOR.' baris'], ',', '"', '\\');
            }

            fputcsv($out, [], ',', '"', '\\');
            // Kolom CSV = kolom tabel kelengkapan di layar (ekspor blok tabel).
            fputcsv($out, [
                'Kode Desa', 'Nama Desa', 'Kecamatan', 'Kelengkapan (%)', 'Kualitas', 'Status',
            ], ',', '"', '\\');

            foreach ($baris as $barisCsv) {
                fputcsv($out, [
                    $barisCsv['kode_desa'],
                    $barisCsv['nama_desa'],
                    $barisCsv['nama_kecamatan'] ?? ($barisCsv['kode_kecamatan'] ?? ''),
                    $barisCsv['kelengkapan_persen'] ?? '',                    
                    $barisCsv['skor_kualitas'] ?? $barisCsv['skor_persen'] ?? '',
                    '',
                ], ',', '"', '\\');
            }

            fclose($out);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);

        return ['response' => $response, 'nama' => $nama, 'dipangkas' => $dipangkas, 'total' => count($semua)];
    }

    // -----------------------------------------------------------------------
    // Pengambilan sumber data
    // -----------------------------------------------------------------------

    /**
     * Isian aktif versi terbit terbaru (denominator + metadata tipe).
     *
     * @return array{tersedia: bool, isian: array<string, array{kode: string, label: string, tipe: string, diarsipkan: bool}>, angka: array<string, array{kode: string, label: string}>}
     */
    public function isianAktif(string $sektor, ?int $versiTerbit): array
    {
        $filters = array_filter([
            'sektor' => $sektor,
            'versi' => $versiTerbit,
        ], static fn ($v) => $v !== null);

        $result = $this->dataspasial->listSkema($filters);
        $kosong = ['tersedia' => false, 'isian' => [], 'angka' => []];

        if (! $result->isData()) {
            return $kosong;
        }

        $item = is_array($result->first()) ? $result->first() : null;
        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : [];
        $isianMentah = is_array($atribut['isian'] ?? null) ? $atribut['isian'] : [];

        if ($isianMentah === []) {
            return $kosong;
        }

        $isian = [];
        $angka = [];

        foreach ($isianMentah as $baris) {
            if (! is_array($baris)) {
                continue;
            }

            $kode = (string) ($baris['kode'] ?? '');

            if ($kode === '') {
                continue;
            }

            $diarsipkan = (bool) ($baris['diarsipkan'] ?? false);
            $tipe = (string) ($baris['tipe'] ?? 'teks');
            $isian[$kode] = [
                'kode' => $kode,
                'label' => (string) ($baris['label'] ?? $kode),
                'tipe' => $tipe,
                'diarsipkan' => $diarsipkan,
            ];

            if ($tipe === 'angka' && ! $diarsipkan) {
                $angka[$kode] = ['kode' => $kode, 'label' => (string) ($baris['label'] ?? $kode)];
            }
        }

        return ['tersedia' => true, 'isian' => $isian, 'angka' => $angka];
    }

    /**
     * Peta kode_kecamatan => nama_kecamatan (config, di-cache service).
     *
     * @return array<string, string>
     */
    public function petaNamaKecamatan(): array
    {
        $peta = [];

        foreach ($this->configApi->kecamatan() as $item) {
            $kode = (string) ($item->kode_kecamatan ?? '');
            $nama = (string) ($item->nama_kecamatan ?? '');

            if ($kode !== '' && $nama !== '') {
                $peta[$kode] = $nama;
            }
        }

        return $peta;
    }

    /**
     * Nomor versi terbit terbaru sektor (dipakai controller untuk metadata aturan).
     */
    public function versiTerbitUntuk(string $sektor): ?int
    {
        return $this->adopsi->versiTerbaruSektor($sektor);
    }

    /**
     * Id sektor dari kode (untuk menyaring baris adopsi).
     */
    public function sektorId(string $kodeSektor): ?int
    {
        $result = $this->dataspasial->listSektor();

        if ($result->isData()) {
            foreach ($result->data as $item) {
                $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : $item;

                if ((string) ($atribut['kode'] ?? '') === $kodeSektor) {
                    $id = $atribut['id'] ?? $item['id'] ?? null;

                    return $id !== null ? (int) $id : null;
                }
            }
        }

        // Fallback: id dari respons skema aktif.
        $skema = $this->dataspasial->listSkema(['sektor' => $kodeSektor]);
        $item = $skema->isData() ? $skema->first() : null;
        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : [];

        return isset($atribut['sektor']['id']) ? (int) $atribut['sektor']['id'] : null;
    }

    /**
     * Baris adopsi untuk satu sektor (baris `sektor_id` null selalu ikut).
     *
     * @param  array<int, array<string, mixed>>  $baris
     * @return array<string, array<string, mixed>> keyed kode_desa
     */
    public function barisAdopsiSektor(array $baris, ?int $sektorId): array
    {
        $hasil = [];

        foreach ($baris as $b) {
            $kode = (string) $b['kode_desa'];

            if ($kode === '') {
                continue;
            }

            if ($b['sektor_id'] !== null && $sektorId !== null && $b['sektor_id'] !== $sektorId) {
                continue;
            }

            $hasil[$kode] ??= [
                'kode_desa' => $kode,
                'nama_desa' => (string) $b['nama_desa'],
                'versi_terpakai' => $b['nomor_versi'],
                'terakhir_ada_data' => $b['terakhir_ada_data'],
                'ada_adopsi' => true,
            ];
        }

        return $hasil;
    }

    /**
     * Susun baris dasar sesuai lingkup kecamatan.
     *
     * @param  array<string, array<string, mixed>>  $barisAdopsi
     * @return array<string, array<string, mixed>>
     */
    public function susunBarisDasar(array $barisAdopsi, string $kodeKecamatan): array
    {
        if ($kodeKecamatan === self::SEMUA_KECAMATAN) {
            return $barisAdopsi;
        }

        $hasil = [];

        foreach ($this->desaWilayah($kodeKecamatan) as $item) {
            $kode = (string) ($item['kode_desa'] ?? '');

            if ($kode === '') {
                continue;
            }

            $hasil[$kode] = $barisAdopsi[$kode] ?? [
                'kode_desa' => $kode,
                'nama_desa' => (string) ($item['nama_desa'] ?? $kode),
                'versi_terpakai' => null,
                'terakhir_ada_data' => null,
                'ada_adopsi' => false,
            ];
        }

        return $hasil;
    }

    /**
     * Daftar desa satu kecamatan lewat `ConfigApiService::desa()`
     * (`GET /api/v1/config/desa?filter[kode_kecamatan]=…`, di-cache service).
     *
     * @return array<int, array<string, string>> kosong bila tidak terbaca/tidak ada
     */
    public function desaWilayah(string $kodeKecamatan): array
    {
        $desa = $this->configApi->desa(['filter[kode_kecamatan]' => $kodeKecamatan]);

        return $desa->map(static function ($item): array {
            $atribut = is_object($item) ? (array) $item : (array) ($item['attributes'] ?? $item);

            return [
                'kode_desa' => (string) ($atribut['kode_desa'] ?? ''),
                'nama_desa' => (string) ($atribut['nama_desa'] ?? ''),
            ];
        })->all();
    }

    /**
     * Bulan periode unik dari `terakhir_ada_data` adopsi, terbaru dulu.
     *
     * @param  array<int, array<string, mixed>>  $barisAdopsi
     * @return array<int, string>
     */
    public function periodeBerdata(array $barisAdopsi): array
    {
        $bulan = [];

        foreach ($barisAdopsi as $b) {
            $terakhir = (string) ($b['terakhir_ada_data'] ?? '');

            if (preg_match('/^(\d{4}-\d{2})/', $terakhir, $m) === 1) {
                $bulan[$m[1]] = true;
            }
        }

        $hasil = array_keys($bulan);
        rsort($hasil, SORT_STRING);

        return $hasil;
    }

    /**
     * Rincian nilai per desa (`nilai?kode_desa=…`, iterasi halaman).
     *
     * @param  array<int, string>  $kodeDesaList
     * @return array<string, array<int, array<string, mixed>>|null> null = rincian tidak terbaca
     */
    public function detailPerDesa(array $kodeDesaList, string $sektor, string $periode): array
    {
        $hasil = [];

        foreach ($kodeDesaList as $kodeDesa) {
            $baris = [];
            $halaman = 1;
            $totalHalaman = 1;

            do {
                $result = $this->dataspasial->listNilai(array_filter([
                    'kode_desa' => $kodeDesa,
                    'sektor' => $sektor,
                    'periode' => $periode,
                    'page' => ['size' => 100, 'number' => $halaman],
                ], static fn ($v) => $v !== null));

                if ($result->isUnavailable()) {
                    Log::warning('KAB-07: rincian nilai desa tidak terbaca', [
                        'kode_desa' => $kodeDesa,
                        'reason' => $result->reason?->value,
                    ]);
                    $hasil[$kodeDesa] = null;

                    continue 2;
                }

                foreach ($result->data as $item) {
                    $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : $item;
                    $baris[] = [
                        'isian_kode' => (string) ($atribut['isian_kode'] ?? ''),
                        'isian_tipe' => (string) ($atribut['isian_tipe'] ?? ''),
                        'nilai' => $atribut['nilai'] ?? null,
                        'sumber_data' => $atribut['sumber_data'] ?? null,
                        'periode' => (string) ($atribut['periode'] ?? ''),
                    ];
                }

                $pagination = $result->pagination();
                $totalHalaman = max(1, (int) ($pagination['total_pages'] ?? 1));
                $halaman++;
            } while ($halaman <= $totalHalaman && $halaman <= 20);

            $hasil[$kodeDesa] = $baris;
        }

        return $hasil;
    }

    // -----------------------------------------------------------------------
    // Perhitungan baris
    // -----------------------------------------------------------------------

    /**
     * Hitung satu baris monitoring.
     *
     * @param  array<string, mixed>  $dasar
     * @param  array<int, array<string, mixed>>  $nilaiRows
     * @param  array{tersedia: bool, isian: array<string, array{kode: string, label: string, tipe: string, diarsipkan: bool}>, angka: array<string, array{kode: string, label: string}>}  $metaIsian
     * @return array<string, mixed>
     */
    public function hitungBaris(
        array $dasar,
        ?int $versiTerbit,
        bool $berdata,
        array $nilaiRows,
        ?int $fallbackTotalIsian,
        bool $rincianTidakTerbaca,
        array $metaIsian,
        string $periode
    ): array {
        $kodeDesa = (string) $dasar['kode_desa'];
        $versiTerpakai = $dasar['versi_terpakai'];

        // Status versi (FR-022): anomali bila nomor versi > terbit terbaru.
        if ($versiTerbit === null || $versiTerpakai === null) {
            $statusVersi = null;
            $versiLabel = '—';
        } elseif ($versiTerpakai > $versiTerbit) {
            $statusVersi = 'anomali';
            $versiLabel = 'Anomali';
        } elseif ($versiTerpakai === $versiTerbit) {
            $statusVersi = 'mutakhir';
            $versiLabel = 'Mutakhir';
        } else {
            $statusVersi = 'tertinggal';
            $versiLabel = 'Tertinggal';
        }

        // Sumber pengisian (FR-023/K-verify §8.1): `impor` bila ada ≥1 baris
        // impor; selain itu nilai dominan; belum mengisi → null ("—").
        $sumber = null;
        $hitungSumber = [];

        foreach ($nilaiRows as $barisNilai) {
            $nilaiSumber = $barisNilai['sumber_data'];

            if (! is_string($nilaiSumber) || $nilaiSumber === '') {
                continue;
            }

            $hitungSumber[$nilaiSumber] = ($hitungSumber[$nilaiSumber] ?? 0) + 1;
        }

        if ($hitungSumber !== []) {
            if (isset($hitungSumber['impor'])) {
                $sumber = 'impor';
            } else {
                arsort($hitungSumber);
                $sumber = (string) array_key_first($hitungSumber);
            }
        }

        // Kelengkapan (FR-024/K-verify §8.6): isian unik terisi / isian aktif.
        $denominator = 0;

        foreach ($metaIsian['isian'] as $isian) {
            if (! $isian['diarsipkan']) {
                $denominator++;
            }
        }

        $kelengkapan = null;

        if ($metaIsian['tersedia'] && $denominator > 0) {
            if ($nilaiRows !== []) {
                $terisi = [];

                foreach ($nilaiRows as $barisNilai) {
                    if ($barisNilai['nilai'] !== null && $barisNilai['nilai'] !== '') {
                        $terisi[$barisNilai['isian_kode']] = true;
                    }
                }

                $pembilang = min(count($terisi), $denominator);
            } elseif ($fallbackTotalIsian !== null) {
                // Fallback ringkasan (K-verify §8.6): total_isian dibatasi denominator.
                $pembilang = min($fallbackTotalIsian, $denominator);
            } elseif ($rincianTidakTerbaca && ! $berdata) {
                // Rincian & ringkasan sama-sama tidak terbaca → "—", bukan nol.
                $kelengkapan = null;
                $pembilang = null;
            } else {
                $pembilang = 0;
            }

            if ($pembilang !== null) {
                $kelengkapan = (int) round(100 * $pembilang / $denominator);
            }
        }

        // Kualitas/skor: tidak ada sumber skor pada data saat ini (tanda data
        // dihapus). Tetap kembalikan null agar UI bisa menampilkan "—".
        return [
            'kode_desa' => $kodeDesa,
            'nama_desa' => (string) $dasar['nama_desa'],
            'status_pengisian' => $berdata ? 'sudah_mengisi' : 'belum_pernah_mengirim',
            'versi_terpakai' => $versiTerpakai,
            'versi_terbit_terbaru' => $versiTerbit,
            'status_versi' => $statusVersi,
            'versi_label' => $versiLabel,
            'sumber' => $sumber,
            'tanda_impor' => $sumber === 'impor',
            'kelengkapan_persen' => $kelengkapan,
            'skor_kualitas' => null,
            'perlu_tindakan' => (! $berdata) || $statusVersi === 'tertinggal',
            'periode' => $periode,
        ];
    }

    // -----------------------------------------------------------------------
    // Payload keadaan
    // -----------------------------------------------------------------------

    /**
     * @param  array{sektor: string, kode_kecamatan: string, periode: string|null, page: int, per_page: int}  $filter
     * @return array<string, mixed>
     */
    public function payloadTidakTersedia(array $filter): array
    {
        return [
            'meta' => [
                'unavailable' => true,
                'empty' => false,
                'message' => 'Data sementara tidak tersedia',
                'validation' => null,
                'sektor' => $filter['sektor'],
                'kode_kecamatan' => $filter['kode_kecamatan'],
                'periode' => $filter['periode'],
                'periods' => [],
                'page' => $filter['page'],
                'per_page' => $filter['per_page'],
                'total' => 0,
                'total_pages' => 1,
            ],
            'kartu' => ['sudah_mengisi' => 0, 'belum_pernah_mengirim' => 0, 'perlu_diperiksa' => 0],
            'tabel' => ['columns' => ['desa', 'kecamatan', 'kelengkapan', 'status', 'kualitas'], 'rows' => []],
            'semua_baris' => [],
        ];
    }

    /**
     * @param  array{sektor: string, kode_kecamatan: string, periode: string|null, page: int, per_page: int}  $filter
     * @param  array<int, string>  $periods
     * @return array<string, mixed>
     */
    public function payloadKosong(array $filter, array $periods, ?string $pesan = null, ?string $periode = null): array
    {
        $payload = $this->payloadTidakTersedia($filter);
        $payload['meta']['unavailable'] = false;
        $payload['meta']['empty'] = true;
        $payload['meta']['message'] = $pesan ?? 'Tidak ada data untuk pilihan ini.';
        $payload['meta']['periods'] = $periods;
        $payload['meta']['periode'] = $periode ?? $filter['periode'];

        return $payload;
    }
}
