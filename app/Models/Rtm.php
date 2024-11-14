<?php

namespace App\Models;

use App\Models\Traits\FilterWilayahTrait;
use Illuminate\Database\Eloquent\Relations\hasOne;

class Rtm extends BaseModel
{
    use FilterWilayahTrait;

    public const KATEGORI_STATISTIK = [
        'bdt' => 'BDT',
    ];

    /** {@inheritdoc} */
    protected $table = 'tweb_rtm';

    public $timestamps = false;

    /**
     * Define a one-to-one relationship.
     *
     * @return hasOne
     */
    public function kepalaKeluarga()
    {
        return $this->hasOne(Penduduk::class, 'id', 'nik_kepala');
    }

    /**
     * Scope query untuk bdt.
     *
     * @return Builder
     */
    public function scopeBdt($query, $value = false)
    {
        if ($value) {
            return $query->where('bdt', '!=', null);
        }

        return $query->where('bdt', '=', null);
    }

    public function scopeCountStatistik($query)
    {
        $query = $this->scopeConfigId($query)
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_rtm.nik_kepala')
            ->join('config', 'config.id', '=', "tweb_penduduk.config_id", 'left')
            ->where('tweb_penduduk.status_dasar', 1);
        if (isset(request('filter')['kabupaten'])) {
            $query->whereRaw('config.kode_kabupaten = '.request('filter')['kabupaten']);
        }
        if (isset(request('filter')['kecamatan'])) {
            $query->whereRaw('config.kode_kecamatan = '.request('filter')['kecamatan']);
        }
        if (isset(request('filter')['desa'])) {
            $query->whereRaw('config.kode_desa = '.request('filter')['desa']);
        }
        $query->groupBy('tweb_rtm.id');

        return $query;
    }

    /**
     * Scope untuk status rtm berdasarkan penduduk hidup.
     */
    public function scopeStatus($query, $value = 1)
    {
        return $query->whereHas('kepalaKeluarga', static function ($query) use ($value) {
            $query->status($value)->where('rtm_level', '1');
        });
    }
}
