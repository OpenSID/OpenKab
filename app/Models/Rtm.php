<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rtm extends BaseModel
{
    public const KATEGORI_STATISTIK = [
        'bdt' => 'BDT',
    ];

    /** {@inheritdoc} */
    protected $table = 'tweb_rtm';

    /**
     * Define a one-to-one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function kepalaKeluarga()
    {
        return $this->hasOne(Penduduk::class, 'id', 'nik_kepala');
    }

    /**
     * Scope query untuk bdt
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
        return $query
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_rtm.nik_kepala')
            ->groupBy('tweb_rtm.id')
        ;
    }
}
