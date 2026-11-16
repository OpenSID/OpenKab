<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\StatusAdopsiDesa;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Tabel status adopsi skema per desa (KAB-03 / US4).
 *
 * Tanggung jawab:
 * 1. Membaca `GET /dataspasial/adopsi` **satu kali** lalu menormalkan nama field
 *    nyata (FR-065).
 * 2. Memetakan `status` API → label UI lewat `StatusAdopsiDesa` (FR-061).
 * 3. Membatasi baris pada satu sektor (FR-047/FR-060).
 * 4. Menyaring, mengurutkan, memaginasikan, dan menyusun ringkasan (FR-062, FR-063).
 * 5. Menyusun baris ekspor = **seluruh** hasil saringan (FR-066).
 *
 * ⚠️ **VERIFIKASI LANGSUNG (2026-10-16)** — koreksi kontrak yang dikunci di sini:
 *   K1 `nomor_versi_skema` (bukan `nomor_versi`)
 *   K2 `status` (bukan `status_adopsi`) dengan hanya `memakai_skema`/`belum_ada_data`
 *   K3 `terakhir_ada_data` (bukan `diadopsi_pada`), format `Y-m-d H:i:s`
 *   K4 respons **tidak memuat** `kode_kecamatan`/`nama_kecamatan` → kolom &
 *       saringan kecamatan tidak ada (Utang U7, research.md R5)
 *   K5 parameter `sektor_id` **diabaikan** API dan paginasi bersifat konyektif
 *       (`total_pages` selalu 1) → penyaringan & paginasi dikerjakan di sini (R6)
 *
 * ⚠️ Satu baris adopsi tidak pernah disimpan di basis data OpenKab (FR-004).
 */
class AdopsiDesaService
{
    /**
     * Batas jumlah baris pada satu berkas ekspor (FR-066).
     *
     * Bila hasil saringan melebihi batas ini, ekspor tetap terbentuk tetapi
     * **menandai** bahwa data dipangkas — berkas separuh tanpa penjelasan
     * ditolak oleh spec.
     */
    public const BATAS_EKSPOR = 5000;

    /**
     * Jumlah baris per halaman pada tabel di dalam dialog (FR-065/SC-012).
     */
    public const PER_HALAMAN = 25;

    public function __construct(
        private readonly DataSpasialApiService $dataspasial,
    ) {}

    // -----------------------------------------------------------------------
    // Pembacaan
    // -----------------------------------------------------------------------

    /**
     * Ambil seluruh baris adopsi dari sumber data (hasil penuh, ter-cache oleh
     * `DataSpasialApiService`).
     *
     * @return array{baris: array<int, array<string, mixed>>, tersedia: bool, total_api: int|null}
     */
    public function ambilSemua(): array
    {
        $result = $this->dataspasial->adopsi();

        if ($result->isUnavailable()) {
            return ['baris' => [], 'tersedia' => false, 'total_api' => null];
        }

        $baris = array_values(array_filter(array_map(
            fn (array $item): ?array => $this->normalisasiBaris($item),
            $result->data
        )));

        $total = $result->pagination()['total'] ?? null;

        return [
            'baris' => $baris,
            'tersedia' => true,
            // Pemeriksaan konsistensi jumlah baris (contracts §5.3).
            'total_api' => $total === null ? null : (int) $total,
        ];
    }

    /**
     * Normalisasi satu item adopsi (nama field nyata K1–K3).
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    private function normalisasiBaris(array $item): ?array
    {
        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : $item;

        if ($atribut === []) {
            return null;
        }

        $statusApi = $atribut['status'] ?? null;
        $nomorVersi = $atribut['nomor_versi_skema'] ?? null;
        $terakhir = $atribut['terakhir_ada_data'] ?? null;

        if ($statusApi !== null && $statusApi !== 'memakai_skema' && $statusApi !== 'belum_ada_data') {
            // FR-061/FR-071: nilai tak dikenal TIDAK pernah dipetakan diam-diam.
            Log::warning('DataSpasial KAB-03: status adopsi tidak dikenal', [
                'config_id' => $atribut['config_id'] ?? null,
                'status' => is_scalar($statusApi) ? $statusApi : gettype($statusApi),
            ]);
        }

        return [
            'config_id' => (int) ($atribut['config_id'] ?? 0),
            'kode_desa' => (string) ($atribut['kode_desa'] ?? ''),
            'nama_desa' => (string) ($atribut['nama_desa'] ?? ''),
            'nomor_versi' => $nomorVersi === null ? null : (int) $nomorVersi,
            'sektor_id' => isset($atribut['sektor_id']) && $atribut['sektor_id'] !== null
                ? (int) $atribut['sektor_id']
                : null,
            'status_api' => is_string($statusApi) ? $statusApi : null,
            'terakhir_ada_data' => is_string($terakhir) && $terakhir !== '' ? $terakhir : null,
        ];
    }

    // -----------------------------------------------------------------------
    // Nomor versi terbit terbaru per sektor (R9)
    // -----------------------------------------------------------------------

    /**
     * Nomor versi **terbit** terbaru sebuah sektor, atau `null` bila belum ada.
     *
     * Memakai `GET /dataspasial/skema?sektor={kode}` yang hasilnya sudah
     * ter-cache oleh service DataSpasial — sehingga tabel adopsi tidak memicu
     * satu permintaan per desa.
     */
    public function versiTerbaruSektor(string $kodeSektor): ?int
    {
        $result = $this->dataspasial->listSkema(['sektor' => $kodeSektor]);

        if ($result->isUnavailable()) {
            Log::warning('DataSpasial KAB-03: versi terbaru sektor tidak terbaca', [
                'sektor' => $kodeSektor,
                'reason' => $result->reason?->value,
            ]);

            return null;
        }

        $item = is_array($result->first()) ? $result->first() : null;

        if ($item === null) {
            return null;
        }

        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : [];
        $versi = is_array($atribut['versi_skema'] ?? null) ? $atribut['versi_skema'] : [];

        // Versi aktif bisa berupa draf; nomor terbit terbaru diambil dari
        // riwayat agar "tertinggal" dibandingkan versi yang benar-benar terbit.
        $riwayat = $this->dataspasial->riwayatSkema(['sektor' => $kodeSektor]);

        if (! $riwayat->isUnavailable()) {
            foreach ($riwayat->data as $baris) {
                $atributVersi = is_array($baris['attributes'] ?? null) ? $baris['attributes'] : [];

                if (($atributVersi['status'] ?? '') === 'terbit') {
                    return (int) ($atributVersi['nomor_versi'] ?? 0);
                }
            }
        }

        return isset($versi['nomor_versi']) ? (int) $versi['nomor_versi'] : null;
    }

    // -----------------------------------------------------------------------
    // Baris tabel
    // -----------------------------------------------------------------------

    /**
     * Baris adopsi untuk satu sektor (FR-047/FR-060).
     *
     * ⚠️ Desa dengan `sektor_id` **null** (`belum_ada_data`) tetap termasuk:
     * desa itu belum pernah menarik skema sehingga relevan untuk semua sektor
     * (Q4 → A, research.md R17). Desa yang `sektor_id`-nya menunjuk sektor lain
     * **tidak** boleh ditampilkan sebagai tertinggal di sektor ini.
     *
     * @param  array<int, array<string, mixed>>  $baris  hasil `ambilSemua()`
     * @return array<int, array<string, mixed>>
     */
    public function barisSektor(array $baris, int $sektorId, ?int $versiTerbaru = null): array
    {
        $hasil = [];

        foreach ($baris as $b) {
            $sektorBaris = $b['sektor_id'];

            if ($sektorBaris !== null && $sektorBaris !== $sektorId) {
                continue;
            }

            $status = StatusAdopsiDesa::dariApi($b['status_api'], $b['nomor_versi'], $versiTerbaru);

            $hasil[] = [
                'config_id' => $b['config_id'],
                'kode_desa' => $b['kode_desa'],
                'nama_desa' => $b['nama_desa'],
                'nomor_versi' => $b['nomor_versi'],
                'nomor_versi_label' => $b['nomor_versi'] === null ? '—' : 'v'.$b['nomor_versi'],
                'status' => $status->value,
                'status_label' => $status->label(),
                'warna_lencana' => $status->warnaLencana(),
                'tandai_tertinggal' => $status->ditandai(),
                'terakhir_ada_data' => $b['terakhir_ada_data'],
                'terakhir_ada_data_label' => $this->formatTanggal($b['terakhir_ada_data']),
            ];
        }

        return $hasil;
    }

    /**
     * Saring + urutkan baris tabel (FR-063/FR-070).
     *
     * @param  array<int, array<string, mixed>>  $baris
     * @param  array<string, mixed>  $filter  `status` (nilai status UI) & `q` (kata kunci)
     * @return array<int, array<string, mixed>>
     */
    public function saring(array $baris, array $filter = []): array
    {
        $status = is_string($filter['status'] ?? null) ? (string) $filter['status'] : '';
        $kata = trim((string) ($filter['q'] ?? ''));

        $hasil = array_values(array_filter($baris, function (array $b) use ($status, $kata): bool {
            if ($status !== '' && $b['status'] !== $status) {
                return false;
            }

            if ($kata === '') {
                return true;
            }

            $haystack = mb_strtolower($b['nama_desa'].' '.$b['kode_desa']);

            return str_contains($haystack, mb_strtolower($kata));
        }));

        // FR-070: desa bermasalah lebih dahulu, lalu menurut nama desa.
        usort($hasil, static function (array $a, array $b): int {
            if ($a['tandai_tertinggal'] !== $b['tandai_tertinggal']) {
                return $b['tandai_tertinggal'] <=> $a['tandai_tertinggal'];
            }

            return strcasecmp((string) $a['nama_desa'], (string) $b['nama_desa']);
        });

        return $hasil;
    }

    /**
     * Paginasi hasil saringan di sisi server OpenKab (FR-065).
     *
     * @param  array<int, array<string, mixed>>  $baris
     * @return array{baris: array<int, array<string, mixed>>, total: int, halaman: int, per_halaman: int, total_halaman: int, ringkasan: array<string, mixed>}
     */
    public function paginate(array $baris, int $halaman = 1, int $perHalaman = self::PER_HALAMAN): array
    {
        $total = count($baris);
        $perHalaman = $perHalaman > 0 ? $perHalaman : self::PER_HALAMAN;
        $totalHalaman = (int) max(1, ceil($total / $perHalaman));
        $halaman = max(1, min($halaman, $totalHalaman));

        return [
            'baris' => array_values(array_slice($baris, ($halaman - 1) * $perHalaman, $perHalaman)),
            'total' => $total,
            'halaman' => $halaman,
            'per_halaman' => $perHalaman,
            'total_halaman' => $totalHalaman,
            'ringkasan' => $this->ringkasan($baris),
        ];
    }

    /**
     * Paginator Laravel untuk view (FR-065).
     *
     * @param  array<int, array<string, mixed>>  $baris
     */
    public function paginator(array $baris, int $halaman, int $perHalaman, array $query = []): LengthAwarePaginator
    {
        $total = count($baris);
        $perHalaman = $perHalaman > 0 ? $perHalaman : self::PER_HALAMAN;

        return new LengthAwarePaginator(
            array_values(array_slice($baris, ($halaman - 1) * $perHalaman, $perHalaman)),
            $total,
            $perHalaman,
            max(1, $halaman),
            ['path' => request()->url(), 'query' => $query]
        );
    }

    /**
     * Ringkasan jumlah pada hasil saringan aktif (FR-062/FR-064).
     *
     * @param  array<int, array<string, mixed>>  $baris
     * @return array<string, mixed>
     */
    public function ringkasan(array $baris): array
    {
        $tertinggal = 0;
        $menunggu = 0;

        foreach ($baris as $b) {
            if ($b['tandai_tertinggal']) {
                $tertinggal++;

                continue;
            }

            if ($b['status'] === StatusAdopsiDesa::MenungguAdopsi) {
                $menunggu++;
            }
        }

        return [
            'total_desa' => count($baris),
            'tertinggal' => $tertinggal,
            'menunggu_adopsi' => $menunggu,
            // Konflik isian belum punya sumber data (Utang U7/U10) → "—",
            // **tidak pernah** 0 agar tidak terlihat seperti "tidak ada konflik".
            'bermasalah' => null,
        ];
    }

    // -----------------------------------------------------------------------
    // Ekspor (FR-066/FR-067)
    // -----------------------------------------------------------------------

    /**
     * Baris ekspor = **seluruh** hasil saringan, bukan hanya halaman aktif
     * (FR-066), dengan penanda bila dipangkas pada batas ekspor.
     *
     * @param  array<int, array<string, mixed>>  $baris
     * @param  array<string, mixed>  $filter
     * @return array{baris: array<int, array<string, mixed>>, dipangkas: bool, total: int}
     */
    public function barisEkspor(array $baris, array $filter = []): array
    {
        $terfilter = $this->saring($baris, $filter);
        $total = count($terfilter);

        return [
            'baris' => array_slice($terfilter, 0, self::BATAS_EKSPOR),
            'dipangkas' => $total > self::BATAS_EKSPOR,
            'total' => $total,
        ];
    }

    /**
     * Daftar status yang boleh dipakai sebagai pilihan saringan (FR-063).
     *
     * @return array<int, array<string, string>>
     */
    public function pilihanStatus(): array
    {
        return array_map(static fn (StatusAdopsiDesa $s): array => [
            'value' => $s->value,
            'label' => $s->label(),
        ], StatusAdopsiDesa::pilihanSaringan());
    }

    /**
     * Format tanggal adopsi; `null`/string rusak → "—" (FR-071).
     */
    private function formatTanggal(?string $nilai): string
    {
        if ($nilai === null || trim($nilai) === '') {
            return '—';
        }

        try {
            return Carbon::parse($nilai)->translatedFormat('d M Y H:i');
        } catch (\Throwable) {
            return '—';
        }
    }
}
