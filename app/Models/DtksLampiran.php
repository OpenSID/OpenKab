<?php

namespace App\Models;

use App\Models\Traits\FilterWilayahTrait;

class DtksLampiran extends BaseModel
{
    use FilterWilayahTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dtks_lampiran';

    /**
     * The guarded with the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The fillable with the model.
     *
     * @var array
     */
    protected $fillable = [
        'judul',
        'keterangan',
        'foto',
        'id_rtm',
    ];

    public function dtks()
    {
        return $this->belongsToMany(DTKS::class, 'dtks_ref_lampiran', 'id_lampiran', 'id_dtks')->withoutGlobalScope(\App\Scopes\ConfigIdScope::class);
    }
}
