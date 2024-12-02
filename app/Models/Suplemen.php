<?php

namespace App\Models;

class Suplemen extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'suplemen';

    public $timestamps = false;

    protected $fillable = [
        'sasaran',
        'nama',
        'keterangan',
        'status',
        'sumber',
        'form_isian',
    ];
}
