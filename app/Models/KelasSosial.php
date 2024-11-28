<?php

namespace App\Models;

class KelasSosial extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tweb_keluarga_sejahtera';

    /**
     * Scope untuk Statistik.
     */
    public function scopeCountStatistik($query, $configId = null)
    {
        $query = $this->scopeFilters($query, request()->input('filter'), 'tgl_daftar')
            ->select(['tweb_keluarga_sejahtera.id', 'tweb_keluarga_sejahtera.nama'])
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->where('tweb_penduduk.status_dasar', '=', 1)
            ->join('tweb_keluarga', 'tweb_keluarga.kelas_sosial', '=', 'tweb_keluarga_sejahtera.id', 'left')
            ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_keluarga.nik_kepala', 'left')
            ->join('config', 'config.id', '=', 'tweb_penduduk.config_id', 'left')
            ->where('tweb_penduduk.status_dasar', 1)
            ->when(session()->has('desa'), function ($query) {
                $query->where('tweb_penduduk.config_id', session('desa.id'));
            })->when($configId, function ($query) use ($configId) {
                $query->where('tweb_penduduk.config_id', $configId);
            });
        if (isset(request('filter')['kabupaten'])) {
            $query->whereRaw('config.kode_kabupaten = '.request('filter')['kabupaten']);
        }
        if (isset(request('filter')['kecamatan'])) {
            $query->whereRaw('config.kode_kecamatan = '.request('filter')['kecamatan']);
        }
        if (isset(request('filter')['desa'])) {
            $query->whereRaw('config.kode_desa = '.request('filter')['desa']);
        }
        $query->groupBy('tweb_keluarga_sejahtera.id', 'tweb_keluarga_sejahtera.nama');

        return $query;
    }
}
