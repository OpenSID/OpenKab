<?php

namespace App\Models;

use App\Models\Traits\FilterWilayahTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pembangunan extends BaseModel
{
    use FilterWilayahTrait;

    /** {@inheritdoc} */
    protected $table = 'pembangunan';

    /** {@inheritdoc} */
    protected $appends = [
        'judul',
        'sumber_dana',
        'anggaran',
        'volume',
        'tahun_anggaran',
        'pelaksana_kegiatan',
        'lokasi',
    ];

    /**
     * Define a one-to-many relationshitweb_penduduk.
     *
     * @return HasMany
     */
    public function rincian()
    {
        return $this->hasMany(PembangunanRincian::class, 'id_pembangunan');
    }

    /**
     * Get the phone associated with the config.
     */
    public function config()
    {
        return $this->hasOne(Config::class, 'id', 'config_id');
    }
}
