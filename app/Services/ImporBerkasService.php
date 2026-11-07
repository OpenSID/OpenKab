<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use OpenSpout\Reader\XLSX\Reader;

/**
 * Membaca berkas impor DataSpasial (XLSX), menormalkan baris, dan menyusun
 * payload serta hasil pratinjau.
 */
class ImporBerkasService
{
    public function __construct(private ConfigApiService $configApiService) {}

    /**
     * Baca baris data dari sheet "Data Impor" (atau sheet pertama yang memuat header kode_desa).
     *
     * @return array<int, array<string, string>>
     */
    public function baca(string $path): array
    {
        $rows = [];

        try {
            $reader = new Reader;
            $reader->open($path);

            foreach ($reader->getSheetIterator() as $sheet) {
                if (in_array(strtolower($sheet->getName()), ['petunjuk', 'daftar desa'], true)) {
                    continue;
                }

                $semuaBaris = [];
                foreach ($sheet->getRowIterator() as $row) {
                    $semuaBaris[] = array_map(static fn ($cell) => trim((string) $cell->getValue()), $row->getCells());
                }

                if ($semuaBaris === []) {
                    continue;
                }

                $indexHeader = $this->indexHeader($semuaBaris);
                if ($indexHeader === -1) {
                    continue;
                }

                $header = array_map(fn ($h) => $this->normalisasiHeader($h), $semuaBaris[$indexHeader]);
                $rows = $this->ambilBaris($semuaBaris, $indexHeader, $header);
                break;
            }

            $reader->close();
        } catch (\Throwable) {
            // Berkas tidak terbaca: kembalikan daftar kosong, validasi di atas yang menangani.
        }

        return $rows;
    }

    /**
     * Pisahkan "Kode - Nama" menjadi kode saja dan tandai baris yang kode desanya valid.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, mixed>  $desaList  Daftar desa dari konfigurasi.
     * @return array<int, array<string, mixed>>
     */
    public function normalisasiKodeDesa(array $rows, mixed $desaList): array
    {
        $kodeValid = array_filter($this->kodeDesaValid($desaList));

        foreach ($rows as $i => $row) {
            $kd = trim((string) ($row['kode_desa'] ?? ($row['desa'] ?? '')));
            if (str_contains($kd, ' - ')) {
                $kd = trim(explode(' - ', $kd, 2)[0]);
            }

            $row['kode_desa'] = $kd;
            $row['nomor_baris'] = $i + 1;
            $row['_kode_valid'] = $kd !== '' && ($kodeValid === [] || in_array($kd, $kodeValid, true));
            $rows[$i] = $row;
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    public function bangunBarisPratinjau(array $rows): array
    {
        $baris = [];
        foreach ($rows as $idx => $row) {
            $nilai = $row['nilai'] ?? $this->ambilNilai($row);

            // Buang key internal yang bukan bagian skema (prefix underscore).
            $nilai = array_filter(
                $nilai,
                static fn ($v, $k) => strpos((string) $k, '_') !== 0,
                ARRAY_FILTER_USE_BOTH
            );

            $baris[] = [
                'nomor_baris' => $row['nomor_baris'] ?? ($idx + 2),
                'kode_desa' => $row['kode_desa'] ?? ($row['desa'] ?? ''),
                'kode_fasilitas' => $row['kode_fasilitas'] ?? '',
                'nama_fasilitas' => $row['nama_fasilitas'] ?? '',
                'periode' => $row['periode'] ?? date('Y'),
                'nilai' => $nilai,
            ];
        }

        return $baris;
    }

    /**
     * Normalisasi respons pratinjau API-06 (JSON:API) menjadi struktur flat untuk frontend.
     *
     * @param  array<int|string, mixed>  $data
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public function normalisasiPratinjau(array $data, array $meta = []): array
    {
        $first = $data[0] ?? $data;
        $attrs = [];
        if (is_array($first) && isset($first['attributes']) && is_array($first['attributes'])) {
            $attrs = $first['attributes'];
        } elseif (is_array($first)) {
            $attrs = $first;
        }

        if (empty($attrs)) {
            $attrs = $meta;
        }

        $ringkasan = $attrs['ringkasan'] ?? [];
        $laporan = $attrs['laporan'] ?? ($attrs['baris'] ?? []);

        $baris = [];
        foreach ((array) $laporan as $item) {
            if (is_object($item)) {
                $item = (array) $item;
            }
            $pesan = $item['pesan'] ?? ($item['alasan'] ?? null);
            if (is_array($pesan)) {
                $pesan = implode('; ', $pesan);
            }
            $baris[] = [
                'nomor_baris' => $item['nomor_baris'] ?? null,
                'kode_desa' => $item['kode_desa'] ?? '',
                'nama_desa' => $item['nama_desa'] ?? '',
                'kode_fasilitas' => $item['kode_fasilitas'] ?? '',
                'nama_fasilitas' => $item['nama_fasilitas'] ?? '',
                'periode' => $item['periode'] ?? '',
                'status' => $item['status'] ?? ($item['keterangan'] ?? ''),
                'pesan' => (string) ($pesan ?? ''),
                'nilai' => $item['nilai'] ?? [],
            ];
        }

        return [
            'impor_id' => $attrs['impor_id'] ?? ($first['impor_id'] ?? ($first['id'] ?? ($attrs['id'] ?? ''))),
            'sektor' => $attrs['sektor'] ?? null,
            'skema_versi' => $attrs['skema_versi'] ?? null,
            'filename' => $attrs['filename'] ?? '',
            'kadaluwarsa_pada' => $attrs['kadaluwarsa_pada'] ?? null,
            'force_overwrite' => $attrs['force_overwrite'] ?? false,
            'mode' => $attrs['mode'] ?? 'sebagian',
            'ringkasan' => [
                'total_baris' => $ringkasan['total_baris'] ?? count($baris),
                'baris_siap' => $ringkasan['baris_siap'] ?? 0,
                'baris_menimpa' => $ringkasan['baris_menimpa'] ?? 0,
                'baris_konflik' => $ringkasan['baris_konflik'] ?? 0,
                'baris_galat' => $ringkasan['baris_galat'] ?? 0,
                'bisa_disimpan' => $ringkasan['bisa_disimpan'] ?? false,
            ],
            'baris' => $baris,
        ];
    }

    /**
     * @param  array<int, array<int, string>>  $semuaBaris
     */
    private function indexHeader(array $semuaBaris): int
    {
        foreach ($semuaBaris as $i => $r) {
            $joined = strtolower(implode(' ', $r));
            if (str_contains($joined, 'kode_desa') && str_contains($joined, 'kode_fasilitas')) {
                return $i;
            }
        }

        foreach ($semuaBaris as $i => $r) {
            if (str_contains(strtolower(implode(' ', $r)), 'kode_desa')) {
                return $i;
            }
        }

        return -1;
    }

    private function normalisasiHeader(mixed $h): string
    {
        $h = strtolower(trim((string) $h));
        $h = str_replace([' ', '-'], '_', $h);

        return (string) preg_replace('/[^a-z0-9_]/', '', $h);
    }

    /**
     * @param  array<int, array<int, string>>  $semuaBaris
     * @param  array<int, string>  $header
     * @return array<int, array<string, string>>
     */
    private function ambilBaris(array $semuaBaris, int $indexHeader, array $header): array
    {
        $rows = [];

        for ($i = $indexHeader + 1; $i < count($semuaBaris); $i++) {
            $data = $semuaBaris[$i];

            if (! $this->adaIsi($data)) {
                continue;
            }

            $row = [];
            foreach ($header as $idx => $col) {
                if ($col === '' || ! isset($data[$idx])) {
                    continue;
                }

                $val = trim((string) $data[$idx]);
                if (strpos($val, '=') === 0) {
                    continue;
                }
                $row[$col] = $val;
            }

            if ($row === []) {
                continue;
            }

            if (isset($row['kode_desa']) && str_contains($row['kode_desa'], ' - ')) {
                $row['kode_desa'] = trim(explode(' - ', $row['kode_desa'], 2)[0]);
            }

            if (isset($row['kode_desa']) && strpos($row['kode_desa'], '=') === 0) {
                continue;
            }

            if (empty($row['kode_desa']) && isset($row['desa'])) {
                unset($row['desa']);
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param  array<int, string>  $data
     */
    private function adaIsi(array $data): bool
    {
        foreach ($data as $v) {
            $val = trim((string) $v);
            if ($val !== '' && strpos($val, '=') !== 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function kodeDesaValid(mixed $desaList): array
    {
        if ($desaList instanceof Collection) {
            $desaList = $desaList->toArray();
        }

        $kode = [];
        foreach ((array) $desaList as $d) {
            if (is_object($d)) {
                $kode[] = (string) ($d->kode_desa ?? $d->kode ?? '');
            } elseif (is_array($d)) {
                $attrs = $d['attributes'] ?? [];
                $kode[] = (string) ($d['kode_desa'] ?? $d['kode'] ?? ($attrs['kode_desa'] ?? ($attrs['kode'] ?? '')));
            }
        }

        return $kode;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function ambilNilai(array $row): array
    {
        $exclude = ['nomor_baris', 'kode_desa', 'desa', 'kode_fasilitas', 'nama_fasilitas', 'periode', 'petunjuk'];
        $nilai = [];

        foreach ($row as $k => $v) {
            if (in_array($k, $exclude, true) || $v === null || $v === '') {
                continue;
            }
            $nilai[$k] = $v;
        }

        return $nilai;
    }
}
