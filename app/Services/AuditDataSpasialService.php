<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SumberEntri;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Pipeline log entri audit DataSpasial (KAB-08 / API-08).
 *
 * Tanggung jawab:
 * 1. Menormalkan filter (sumber, aksi, pelaku, sektor, desa, kecamatan,
 *    rentang tanggal) menjadi parameter yang diterima API (#474).
 * 2. Membaca daftar entri dari `GET /dataspasial/audit` lalu memetakan
 *    field API → bentuk tabel di layar.
 * 3. Membaca ringkasan per sumber dari `GET /dataspasial/audit/ringkasan`.
 * 4. Menyusun baris ekspor yang **identik** dengan isi layar.
 *
 * ⚠️ Entri audit **tidak pernah** disimpan di basis data OpenKab — hanya
 * dibaca lewat API (pola FR-004 KAB-05/KAB-07).
 */
class AuditDataSpasialService
{
    /**
     * Jumlah baris per halaman.
     */
    public const PER_HALAMAN = 25;

    /**
     * Batas jumlah baris yang diekspor ke satu berkas Excel.
     */
    public const BATAS_EKSPOR = 5000;

    /**
     * Batas halaman yang boleh diambil saat mengekspor.
     */
    public const BATAS_HALAMAN_EKSPOR = 200;

    /**
     * Nilai `sumber` untuk "semua sumber" (tidak difilter).
     */
    public const SEMUA_SUMBER = '';

    public function __construct(
        public readonly DataSpasialApiService $dataspasial,
    ) {}

    // -----------------------------------------------------------------------
    // Filter
    // -----------------------------------------------------------------------

    /**
     * Filter ternormalisasi dari query string (validasi dilakukan controller).
     *
     * @param  array<string, mixed>  $query
     * @return array{sumber: string, aksi: string, pelaku: string, sektor: string, kode_kecamatan: string, kode_desa: string, waktu_dari: string|null, waktu_sampai: string|null, page: int, per_page: int}
     */
    public function filter(array $query): array
    {
        return [
            'sumber' => trim((string) ($query['sumber'] ?? '')),
            'aksi' => trim((string) ($query['aksi'] ?? '')),
            'pelaku' => trim((string) ($query['pelaku'] ?? '')),
            'sektor' => trim((string) ($query['sektor'] ?? '')),
            'kode_kecamatan' => trim((string) ($query['kode_kecamatan'] ?? '')),
            'kode_desa' => trim((string) ($query['kode_desa'] ?? '')),
            'waktu_dari' => $this->tanggal((string) ($query['waktu_dari'] ?? '')),
            'waktu_sampai' => $this->tanggal((string) ($query['waktu_sampai'] ?? '')),
            'page' => max(1, (int) ($query['page'] ?? 1)),
            'per_page' => max(1, min(100, (int) ($query['per_page'] ?? self::PER_HALAMAN))),
        ];
    }

    /**
     * Parameter query yang diteruskan ke API audit.
     *
     * Filter wilayah desa lebih spesifik daripada kecamatan: bila desa
     * dipilih, filter kecamatan ikut dikirim agar keduanya konsisten.
     *
     * @param  array<string, mixed>  $filter
     * @return array<string, mixed>
     */
    public function queryApi(array $filter): array
    {
        $query = array_filter([
            'sumber' => $filter['sumber'] !== '' ? $filter['sumber'] : null,
            'aksi' => $filter['aksi'] !== '' ? $filter['aksi'] : null,
            'pelaku' => $filter['pelaku'] !== '' ? $filter['pelaku'] : null,
            'sektor' => $filter['sektor'] !== '' ? $filter['sektor'] : null,
            'kode_kecamatan' => $filter['kode_kecamatan'] !== '' ? $filter['kode_kecamatan'] : null,
            'kode_desa' => $filter['kode_desa'] !== '' ? $filter['kode_desa'] : null,
            'waktu_dari' => $filter['waktu_dari'],
            'waktu_sampai' => $filter['waktu_sampai'],
        ], static fn ($value): bool => $value !== null);

        return $query;
    }

    // -----------------------------------------------------------------------
    // Pembacaan
    // -----------------------------------------------------------------------

    /**
     * Daftar entri audit untuk satu filter.
     *
     * @param  array<string, mixed>  $filter
     * @return array{tersedia: bool, baris: array<int, array<string, mixed>>, total: int, page: int, per_page: int, total_pages: int}
     */
    public function daftar(array $filter): array
    {
        $query = $this->queryApi($filter);
        $query['page[number]'] = $filter['page'];
        $query['page[size]'] = $filter['per_page'];

        $result = $this->dataspasial->audit($query);

        if ($result->isUnavailable()) {
            Log::warning('DataSpasial KAB-08: log audit tidak terbaca', [
                'reason' => $result->reason?->value,
                'filter' => $this->queryApi($filter),
            ]);

            return [
                'tersedia' => false,
                'baris' => [],
                'total' => 0,
                'page' => $filter['page'],
                'per_page' => $filter['per_page'],
                'total_pages' => 1,
            ];
        }

        $baris = [];

        foreach ($result->data as $item) {
            $normal = $this->normalisasiBaris($item);

            if ($normal !== null) {
                $baris[] = $normal;
            }
        }

        $pagination = $result->pagination();
        $total = (int) ($pagination['total'] ?? count($baris));
        $totalPages = (int) ($pagination['total_pages'] ?? max(1, (int) ceil($total / max(1, $filter['per_page']))));

        return [
            'tersedia' => true,
            'baris' => $baris,
            'total' => $total,
            'page' => $filter['page'],
            'per_page' => $filter['per_page'],
            'total_pages' => max(1, $totalPages),
        ];
    }

    /**
     * Normalisasi satu entri audit dari bentuk API (JSON:API) ke baris tabel.
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

        $sumber = SumberEntri::coerce((string) ($atribut['sumber'] ?? ''));
        $desa = is_array($atribut['desa'] ?? null) ? $atribut['desa'] : [];
        $pelaku = is_array($atribut['pelaku'] ?? null) ? $atribut['pelaku'] : [];
        $sasaran = is_array($atribut['sasaran'] ?? null) ? $atribut['sasaran'] : [];
        $sektor = is_array($sasaran['sektor'] ?? null) ? $sasaran['sektor'] : [];

        return [
            'id' => (string) ($item['id'] ?? $atribut['id'] ?? ''),
            'waktu' => $this->waktuTerbaca((string) ($atribut['waktu'] ?? '')),
            'sumber' => $sumber?->value ?? '',
            'sumber_label' => $sumber?->label() ?? '—',
            'sumber_badge' => $sumber?->badgeClass() ?? 'badge-secondary',
            'pelaku_id' => $pelaku['id'] ?? null,
            'pelaku_nama' => (string) ($pelaku['nama'] ?? ''),
            'kode_desa' => (string) ($desa['kode_desa'] ?? ''),
            'nama_desa' => (string) ($desa['nama_desa'] ?? ''),
            'kode_kecamatan' => (string) ($desa['kode_kecamatan'] ?? ''),
            'nama_kecamatan' => (string) ($desa['nama_kecamatan'] ?? ''),
            'aksi' => (string) ($atribut['aksi'] ?? ''),
            'aksi_label' => $this->labelAksi((string) ($atribut['aksi'] ?? '')),
            'entitas' => (string) ($sasaran['entitas'] ?? ''),
            'sektor_kode' => (string) ($sektor['kode'] ?? ''),
            'sektor_nama' => (string) ($sektor['nama'] ?? ''),
            'periode' => (string) ($sasaran['periode'] ?? ''),
            'ringkasan_sebelum' => is_array($atribut['ringkasan_sebelum'] ?? null) ? $atribut['ringkasan_sebelum'] : null,
            'ringkasan_sesudah' => is_array($atribut['ringkasan_sesudah'] ?? null) ? $atribut['ringkasan_sesudah'] : null,
            'perubahan' => $this->ringkasanPerubahan(
                is_array($atribut['ringkasan_sebelum'] ?? null) ? $atribut['ringkasan_sebelum'] : null,
                is_array($atribut['ringkasan_sesudah'] ?? null) ? $atribut['ringkasan_sesudah'] : null
            ),
        ];
    }

    /**
     * Ringkasan jumlah entri per sumber (untuk kartu ringkasan).
     *
     * @param  array<string, mixed>  $filter
     * @return array{tersedia: bool, per_sumber: array<string, int>, total: int, per_desa: array<int, array<string, mixed>>}
     */
    public function ringkasan(array $filter): array
    {
        $query = array_filter([
            'kode_kecamatan' => $filter['kode_kecamatan'] !== '' ? $filter['kode_kecamatan'] : null,
            'kode_desa' => $filter['kode_desa'] !== '' ? $filter['kode_desa'] : null,
            'waktu_dari' => $filter['waktu_dari'],
            'waktu_sampai' => $filter['waktu_sampai'],
        ], static fn ($value): bool => $value !== null);

        $result = $this->dataspasial->auditRingkasan($query);

        if ($result->isUnavailable()) {
            Log::warning('DataSpasial KAB-08: ringkasan audit tidak terbaca', [
                'reason' => $result->reason?->value,
            ]);

            return ['tersedia' => false, 'per_sumber' => [], 'total' => 0, 'per_desa' => []];
        }

        $atribut = $this->atributRingkasan($result->data);
        $perSumber = [];
        $total = 0;

        foreach ((array) ($atribut['per_sumber'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $sumber = (string) ($row['sumber'] ?? '');
            $jumlah = (int) ($row['jumlah'] ?? 0);

            if ($sumber === '') {
                continue;
            }

            $perSumber[$sumber] = $jumlah;
            $total += $jumlah;
        }

        $perDesa = [];

        foreach ((array) ($atribut['per_desa'] ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $perDesa[] = [
                'kode_desa' => (string) ($row['kode_desa'] ?? ''),
                'nama_desa' => (string) ($row['nama_desa'] ?? ''),
                'jumlah' => (int) ($row['jumlah'] ?? 0),
            ];
        }

        return [
            'tersedia' => true,
            'per_sumber' => $perSumber,
            'total' => $total,
            'per_desa' => $perDesa,
        ];
    }

    /**
     * Detail satu entri audit (nilai sebelum/sesudah penuh).
     *
     * @return array<string, mixed>|null
     */
    public function detail(string $id): ?array
    {
        $result = $this->dataspasial->auditDetail($id);

        if ($result->isUnavailable()) {
            Log::warning('DataSpasial KAB-08: detail audit tidak terbaca', [
                'id' => $id,
                'reason' => $result->reason?->value,
            ]);

            return null;
        }

        if ($result->httpStatus === 404) {
            return null;
        }

        $item = $result->first();

        if (! is_array($item)) {
            return null;
        }

        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : $item;
        $sumber = SumberEntri::coerce((string) ($atribut['sumber'] ?? ''));
        $desa = is_array($atribut['desa'] ?? null) ? $atribut['desa'] : [];
        $pelaku = is_array($atribut['pelaku'] ?? null) ? $atribut['pelaku'] : [];
        $sasaran = is_array($atribut['sasaran'] ?? null) ? $atribut['sasaran'] : [];
        $sektor = is_array($sasaran['sektor'] ?? null) ? $sasaran['sektor'] : [];

        $sebelum = is_array($atribut['sebelum'] ?? null) ? $atribut['sebelum'] : null;
        $sesudah = is_array($atribut['sesudah'] ?? null) ? $atribut['sesudah'] : null;

        return [
            'id' => (string) ($item['id'] ?? $atribut['id'] ?? $id),
            'waktu' => $this->waktuTerbaca((string) ($atribut['waktu'] ?? '')),
            'sumber' => $sumber?->value ?? '',
            'sumber_label' => $sumber?->label() ?? '—',
            'sumber_badge' => $sumber?->badgeClass() ?? 'badge-secondary',
            'pelaku_nama' => (string) ($pelaku['nama'] ?? ''),
            'kode_desa' => (string) ($desa['kode_desa'] ?? ''),
            'nama_desa' => (string) ($desa['nama_desa'] ?? ''),
            'kode_kecamatan' => (string) ($desa['kode_kecamatan'] ?? ''),
            'nama_kecamatan' => (string) ($desa['nama_kecamatan'] ?? ''),
            'aksi' => (string) ($atribut['aksi'] ?? ''),
            'aksi_label' => $this->labelAksi((string) ($atribut['aksi'] ?? '')),
            'entitas' => (string) ($sasaran['entitas'] ?? ''),
            'sektor_kode' => (string) ($sektor['kode'] ?? ''),
            'sektor_nama' => (string) ($sektor['nama'] ?? ''),
            'periode' => (string) ($sasaran['periode'] ?? ''),
            'sebelum' => $sebelum,
            'sesudah' => $sesudah,
            'perubahan' => $this->bandingkanIsian($sebelum, $sesudah),
        ];
    }

    // -----------------------------------------------------------------------
    // Penyusunan tampilan
    // -----------------------------------------------------------------------

    /**
     * Susun seluruh payload halaman (kartu ringkasan + tabel + meta).
     *
     * @param  array<string, mixed>  $query  Query mentah (belum dinormalisasi).
     * @return array<string, mixed>
     */
    public function buildHalaman(array $query): array
    {
        $filter = $this->filter($query);
        $daftar = $this->daftar($filter);
        $ringkasan = $this->ringkasan($filter);

        if (! $daftar['tersedia']) {
            return $this->payloadTidakTersedia($filter);
        }

        if ($daftar['total'] === 0) {
            return $this->payloadKosong($filter);
        }

        $kartu = $this->kartuRingkasan($ringkasan);

        return [
            'meta' => [
                'unavailable' => false,
                'empty' => false,
                'message' => null,
                'validation' => null,
                'filter' => $filter,
                'page' => $daftar['page'],
                'per_page' => $daftar['per_page'],
                'total' => $daftar['total'],
                'total_pages' => $daftar['total_pages'],
            ],
            'kartu' => $kartu,
            'tabel' => [
                'columns' => ['waktu', 'pelaku', 'sumber', 'aksi', 'sasaran', 'perubahan'],
                'rows' => $daftar['baris'],
            ],
            'ringkasan' => [
                'tersedia' => $ringkasan['tersedia'],
                'per_desa' => $ringkasan['per_desa'],
            ],
        ];
    }

    /**
     * Kartu ringkasan jumlah entri per sumber.
     *
     * @param  array<string, mixed>  $ringkasan
     * @return array<string, mixed>
     */
    private function kartuRingkasan(array $ringkasan): array
    {
        $perSumber = $ringkasan['per_sumber'];

        return [
            'openkab' => (int) ($perSumber[SumberEntri::Openkab] ?? 0),
            'opensid' => (int) ($perSumber[SumberEntri::Opensid] ?? 0),
            'total' => (int) $ringkasan['total'],
            'tersedia' => (bool) $ringkasan['tersedia'],
        ];
    }

    /**
     * Baris ekspor — isi kolom identik dengan kolom tabel di layar.
     *
     * @param  array<string, mixed>  $filter
     * @return array{baris: array<int, array<string, mixed>>, terpotong: bool}
     */
    public function barisEkspor(array $filter): array
    {
        $filterEkspor = $filter;
        $filterEkspor['per_page'] = 100;
        $filterEkspor['page'] = 1;

        $baris = [];
        $terpotong = false;
        $total = null;

        for ($page = 1; $page <= self::BATAS_HALAMAN_EKSPOR; $page++) {
            $filterEkspor['page'] = $page;

            $hasil = $this->daftar($filterEkspor);

            if (! $hasil['tersedia']) {
                break;
            }

            $total ??= $hasil['total'];

            foreach ($hasil['baris'] as $b) {
                $baris[] = $b;
            }

            if (count($baris) >= self::BATAS_EKSPOR) {
                $terpotong = true;
                $baris = array_slice($baris, 0, self::BATAS_EKSPOR);

                break;
            }

            if ($hasil['baris'] === [] || $page >= $hasil['total_pages']) {
                break;
            }
        }

        return ['baris' => $baris, 'terpotong' => $terpotong];
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    /**
     * Label ramah untuk nilai aksi.
     */
    private function labelAksi(string $aksi): string
    {
        return match ($aksi) {
            'create' => 'Tambah',
            'update' => 'Ubah',
            'delete' => 'Hapus',
            'import' => 'Impor',
            'publish' => 'Terbitkan',
            default => $aksi !== '' ? $aksi : '—',
        };
    }

    /**
     * Ringkasan perubahan per isian untuk tampilan tabel.
     *
     * @param  array<string, mixed>|null  $sebelum
     * @param  array<string, mixed>|null  $sesudah
     * @return array<int, array{key: string, label: string, sebelum: string, sesudah: string, berubah: bool}>
     */
    private function ringkasanPerubahan(?array $sebelum, ?array $sesudah): array
    {
        return array_slice($this->bandingkanIsian($sebelum, $sesudah), 0, 3);
    }

    /**
     * Bandingkan nilai sebelum & sesudah per isian.
     *
     * @param  array<string, mixed>|null  $sebelum
     * @param  array<string, mixed>|null  $sesudah
     * @return array<int, array{key: string, label: string, sebelum: string, sesudah: string, berubah: bool}>
     */
    private function bandingkanIsian(?array $sebelum, ?array $sesudah): array
    {
        $sebelum ??= [];
        $sesudah ??= [];

        $kunci = array_values(array_unique(array_merge(array_keys($sebelum), array_keys($sesudah))));
        $hasil = [];

        foreach ($kunci as $k) {
            $nilaiSebelum = $sebelum[$k] ?? null;
            $nilaiSesudah = $sesudah[$k] ?? null;

            $hasil[] = [
                'key' => (string) $k,
                'label' => (string) $k,
                'sebelum' => $this->nilaiTerbaca($nilaiSebelum),
                'sesudah' => $this->nilaiTerbaca($nilaiSesudah),
                'berubah' => $this->nilaiTerbaca($nilaiSebelum) !== $this->nilaiTerbaca($nilaiSesudah),
            ];
        }

        return $hasil;
    }

    /**
     * Ubah nilai apa adanya menjadi teks yang aman ditampilkan.
     */
    private function nilaiTerbaca(mixed $nilai): string
    {
        if ($nilai === null) {
            return '—';
        }

        if (is_bool($nilai)) {
            return $nilai ? 'Ya' : 'Tidak';
        }

        if (is_array($nilai)) {
            return (string) json_encode($nilai, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_scalar($nilai)) {
            return (string) $nilai;
        }

        return '—';
    }

    /**
     * Waktu ISO-8601 → format tampilan lokal.
     */
    private function waktuTerbaca(string $waktu): string
    {
        if ($waktu === '') {
            return '—';
        }

        try {
            return Carbon::parse($waktu)->format('d-m-Y H:i:s');
        } catch (\Throwable) {
            return $waktu;
        }
    }

    /**
     * Normalisasi parameter tanggal menjadi `Y-m-d` (null bila kosong/tidak sah).
     */
    private function tanggal(string $nilai): ?string
    {
        $nilai = trim($nilai);

        if ($nilai === '') {
            return null;
        }

        try {
            return Carbon::parse($nilai)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Ambil `attributes` dari respons ringkasan (bisa `data` objek tunggal).
     *
     * @param  array<int, array<string, mixed>>  $data
     * @return array<string, mixed>
     */
    private function atributRingkasan(array $data): array
    {
        $item = $data[0] ?? null;

        if (! is_array($item)) {
            return [];
        }

        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : $item;

        return $atribut;
    }

    /**
     * Payload saat sumber audit tidak tersedia (kontrak `meta.unavailable`).
     *
     * @param  array<string, mixed>  $filter
     * @return array<string, mixed>
     */
    private function payloadTidakTersedia(array $filter): array
    {
        return [
            'meta' => [
                'unavailable' => true,
                'empty' => false,
                'message' => 'Data sementara tidak tersedia',
                'validation' => null,
                'filter' => $filter,
                'page' => (int) ($filter['page'] ?? 1),
                'per_page' => (int) ($filter['per_page'] ?? self::PER_HALAMAN),
                'total' => 0,
                'total_pages' => 1,
            ],
            'kartu' => ['openkab' => 0, 'opensid' => 0, 'total' => 0, 'tersedia' => false],
            'tabel' => ['columns' => ['waktu', 'pelaku', 'sumber', 'aksi', 'sasaran', 'perubahan'], 'rows' => []],
            'ringkasan' => ['tersedia' => false, 'per_desa' => []],
        ];
    }
}
