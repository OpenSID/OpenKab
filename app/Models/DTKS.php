<?php

namespace App\Models;

class DTKS extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dtks';

    /**
     * The timestamps for the model.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'config_id',
    ];

    public function config()
    {
        return $this->belongsTo(Config::class, 'config_id');
    }
}
