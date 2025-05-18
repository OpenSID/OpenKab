<?php

namespace App\Services;

class StatistikPendudukApiService extends BaseApiService
{
    /**
     * Get Unique Desa.
     */
    public function laporanBulanan($tahun, $bulan)
    {
        // $filters['kode_kabupaten'] = 5306;
        $kode_kabupaten = session('kabupaten.kode_kabupaten');

        // Panggil API dan ambil data
        $data = $this->apiRequest("/api/v1/statistik/laporan-bulanan/{$tahun}/{$bulan}/{$kode_kabupaten}");
        if (! $data) {
            return collect([]);
        }

        return $data;
    }

    /**
     * Get Unique Desa.
     */
    public function sumberData($rincian, $tipe, $tahun, $bulan)
    {
        $kode_kabupaten = session('kabupaten.kode_kabupaten');

        // Panggil API dan ambil data
        $data = $this->apiRequest("/api/v1/statistik/laporan-bulanan/sumber-data/{$rincian}/{$tipe}/{$tahun}/{$bulan}/{$kode_kabupaten}");
        if (! $data) {
            return collect([]);
        }

        return $data;
    }

    /**
     * Get Unique Desa.
     */
    public function logPenduduk(array $filters = [])
    {
        // Panggil API dan ambil data
        $data = $this->apiRequest('/api/v1/statistik/laporan-bulanan/log-penduduk', $filters);
        if (! $data) {
            return collect([]);
        }

        return $data;
    }
}
