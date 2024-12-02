<?php

namespace App\Models;

class SuplemenTerdata extends BaseModel
{

    public const PENDUDUK = 1;
    public const KELUARGA = 2;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'suplemen_terdata';

    /**
     * The timestamps for the model.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The guarded with the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['suplemen', 'penduduk'];

    public function suplemen()
    {
        return $this->belongsTo(Suplemen::class, 'id_suplemen');
    }
}
