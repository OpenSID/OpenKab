<?php

namespace App\Models;

class Potensi extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prodeskel_potensi';

    /**
     * The casts with the model.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'json',
    ];
}
