<?php

namespace App\Models;

use App\Models\Traits\ConfigIdTrait;
use Illuminate\Database\Eloquent\Model;

class KelasSosial extends BaseModel
{
    use ConfigIdTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tweb_keluarga_sejahtera';

    /**
     * Scope untuk Statistik
     */
    public function scopeCountStatistik($query)
    {
        return $query
            ->select(['tweb_keluarga_sejahtera.id', 'tweb_keluarga_sejahtera.nama'])
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->where('tweb_penduduk.status_dasar', '=', 1)
            ->join('tweb_keluarga', 'tweb_keluarga.kelas_sosial', '=', 'tweb_keluarga_sejahtera.id', 'left')
            ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_keluarga.nik_kepala', 'left')
            ->groupBy('tweb_keluarga_sejahtera.id');
    }
}
