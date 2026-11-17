<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * Halaman Status Desa (KAB-09 / API-09): daftar desa beserta status pengisian Data Spasialnya,
 * dan nonaktifkan/aktifkan pengisian lewat `PUT /dataspasial/desa/{config_id}/status`.
 *
 * Seluruh data dibaca/ditulis lewat `DataSpasialApiService`; OpenKab tidak menyimpan status di
 * basis datanya sendiri (FR-004). Penulisan sukses otomatis menginvalidasi cache baca.
 */
class StatusDesaService
{
    public const PER_HALAMAN = 25;

    public function __construct(
        public readonly DataSpasialApiService $dataspasial,
    ) {}

    /**
     * Satu halaman daftar desa yang sudah dinormalkan.
     *
     * @param  array<string, mixed>  $filter  kode_kecamatan, status (aktif|nonaktif), page
     * @return array{tersedia: bool, baris: array<int, array<string, mixed>>, halaman: int, total_halaman: int, total: int, per_halaman: int, ringkasan: array<string, int>}
     */
    public function daftar(array $filter): array
    {
        $halaman = max(1, (int) ($filter['page'] ?? 1));
        $query = array_filter([
            'kode_kecamatan' => $this->teks($filter['kode_kecamatan'] ?? null),
            'status' => in_array($filter['status'] ?? null, ['aktif', 'nonaktif'], true) ? $filter['status'] : null,
            'page' => ['number' => $halaman, 'size' => self::PER_HALAMAN],
        ], static fn ($v) => $v !== null);

        $result = $this->dataspasial->listDesa($query);

        if ($result->isUnavailable()) {
            return [
                'tersedia' => false,
                'baris' => [],
                'halaman' => $halaman,
                'total_halaman' => 1,
                'total' => 0,
                'per_halaman' => self::PER_HALAMAN,
                'ringkasan' => ['total' => 0, 'nonaktif' => 0],
            ];
        }

        $baris = array_values(array_filter(array_map(
            fn (array $item): ?array => $this->normalisasiBaris($item),
            $result->data
        )));
        $pagination = $result->pagination();

        return [
            'tersedia' => true,
            'baris' => $baris,
            'halaman' => (int) ($pagination['current_page'] ?? $halaman),
            'total_halaman' => max(1, (int) ($pagination['total_pages'] ?? 1)),
            'total' => (int) ($pagination['total'] ?? count($baris)),
            'per_halaman' => (int) ($pagination['per_page'] ?? self::PER_HALAMAN),
            'ringkasan' => [
                'total' => count($baris),
                'nonaktif' => count(array_filter($baris, static fn (array $b): bool => ! $b['isian_aktif'])),
            ],
        ];
    }

    /**
     * Nonaktifkan (`$aktif=false`, alasan wajib) atau aktifkan kembali pengisian satu desa.
     */
    public function ubahStatus(int $configId, bool $aktif, ?string $alasan): DataSpasialResult
    {
        return $this->dataspasial->ubahStatusDesa($configId, $aktif, $alasan);
    }

    /**
     * Pesan untuk flash setelah ubah status berhasil.
     */
    public function pesanBerhasil(DataSpasialResult $result, bool $aktif): string
    {
        $item = is_array($result->first()) ? $result->first() : [];
        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : [];
        $nama = (string) ($atribut['nama_desa'] ?? 'Desa');

        return $aktif
            ? "Pengisian Data Spasial {$nama} telah diaktifkan kembali."
            : "Pengisian Data Spasial {$nama} telah dinonaktifkan. Operator desa tidak dapat mengubah data sampai diaktifkan kembali.";
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function pilihanStatus(): array
    {
        return [
            ['value' => '', 'label' => 'Semua Status'],
            ['value' => 'aktif', 'label' => 'Aktif'],
            ['value' => 'nonaktif', 'label' => 'Nonaktif'],
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null
     */
    private function normalisasiBaris(array $item): ?array
    {
        $atribut = is_array($item['attributes'] ?? null) ? $item['attributes'] : $item;

        if ($atribut === [] || ! isset($atribut['config_id'])) {
            return null;
        }

        $aktif = (bool) ($atribut['isian_aktif'] ?? true);

        return [
            'config_id' => (int) $atribut['config_id'],
            'kode_desa' => (string) ($atribut['kode_desa'] ?? ''),
            'nama_desa' => (string) ($atribut['nama_desa'] ?? ''),
            'kode_kecamatan' => (string) ($atribut['kode_kecamatan'] ?? ''),
            'nama_kecamatan' => (string) ($atribut['nama_kecamatan'] ?? ($atribut['kode_kecamatan'] ?? '')),
            'isian_aktif' => $aktif,
            'status_label' => $aktif ? 'Aktif' : 'Nonaktif',
            'warna_lencana' => $aktif ? 'success' : 'danger',
            'alasan_nonaktif' => $this->teks($atribut['alasan_nonaktif'] ?? null),
            'dinonaktifkan_oleh' => $this->teks($atribut['dinonaktifkan_oleh'] ?? null),
            'dinonaktifkan_pada' => $this->teks($atribut['dinonaktifkan_pada'] ?? null),
            'dinonaktifkan_pada_label' => $this->formatTanggal($atribut['dinonaktifkan_pada'] ?? null),
        ];
    }

    private function teks(mixed $nilai): ?string
    {
        return is_string($nilai) && trim($nilai) !== '' ? trim($nilai) : null;
    }

    private function formatTanggal(mixed $nilai): string
    {
        if (! is_string($nilai) || trim($nilai) === '') {
            return '—';
        }

        try {
            return Carbon::parse($nilai)->translatedFormat('d M Y H:i');
        } catch (\Throwable) {
            return '—';
        }
    }
}
