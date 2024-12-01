<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KaderPemberdayaanMasyarakat extends BaseModel
{
    /** {@inheritdoc} */
    protected $table = 'kader_pemberdayaan_masyarakat';

    /**
     * {@inheritDoc}
     */
    protected $with = [
        'config_id',
        'penduduk_id',
        'kursus',
        'bidang',
        'keterangan',
    ];

    public function pendudukKursus(): BelongsTo
    {
        return $this->belongsTo(PendudukKursus::class, 'kursus');
    }
}
