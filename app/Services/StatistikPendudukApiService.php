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
        $kode_kabupaten = session('kode_kabupaten');
        $kode_kecamatan = session('kode_kecamatan');
        $config_id = $this->getConfigId();

        // Panggil API dan ambil data
        $data = $this->apiRequest("/api/v1/statistik/laporan-bulanan", [
            'filter[tahun]' => $tahun,
            'filter[bulan]' => $bulan,
            'filter[kode_kabupaten]' => $kode_kabupaten,
            'filter[kode_kecamatan]' => $kode_kecamatan,
            'filter[config_desa]' => $config_id,
            'kode_kabupaten' => $kode_kabupaten,
            'kode_kecamatan' => $kode_kecamatan,
            'config_desa' => $config_id,
        ]);
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
        $kode_kabupaten = session('kode_kabupaten');
        $kode_kecamatan = session('kode_kecamatan');
        $config_id = $this->getConfigId();

        // Panggil API dan ambil data
        $data = $this->apiRequest("/api/v1/statistik/laporan-bulanan/sumber-data", [
            'filter[rincian]' => $rincian,
            'filter[tipe]' => $tipe,
            'filter[tahun]' => $tahun,
            'filter[bulan]' => $bulan,
            'filter[kode_kabupaten]' => $kode_kabupaten,
            'filter[kode_kecamatan]' => $kode_kecamatan,
            'filter[config_desa]' => $config_id,
            'kode_kabupaten' => $kode_kabupaten,
            'kode_kecamatan' => $kode_kecamatan,
            'config_desa' => $config_id,
        ]);
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

    public function getConfigId()
    {
        $config = (new ConfigApiService)->desaConfig([
            'filter[kode_desa]' => session('kode_desa')
        ]);

        if($config->count() > 0)
        {
            $data = $config[0];
            return $data->id ?? null;
        }else{
            return null;
        }
    }
}
