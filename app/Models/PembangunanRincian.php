<?php

namespace App\Models;

use App\Models\Traits\ConfigIdTrait;
use App\Models\Traits\FilterWilayahTrait;

class PembangunanRincian extends BaseModel
{
    use FilterWilayahTrait;
    use ConfigIdTrait;

    /** {@inheritdoc} */
    protected $table = 'pembangunan_ref_dokumentasi';

    /** {@inheritdoc} */
    protected $appends = [
        'persentase',
        'keterangan',
        'created_at',
    ];

    /**
     * Define a one-to-many relationship.
     *
     * @return BelongsTo
     */
    public function pembangunan()
    {
        return $this->belongsTo(Pembangunan::class, 'id_pembangunan');
    }

    /**
     * Get the phone associated with the config.
     */
    public function config()
    {
        return $this->hasOne(Config::class, 'id', 'config_id');
    }
}
