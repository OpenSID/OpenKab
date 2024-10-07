<?php

namespace App\Models;

class KIA extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kia';

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
        'no_kia',
        'ibu_id',
        'anak_id',
        'hari_perkiraan_lahir',
    ];

    public function ibu()
    {
        return $this->belongsTo(Penduduk::class, 'ibu_id');
    }

    public function anak()
    {
        return $this->belongsTo(Penduduk::class, 'anak_id');
    }

    /**
     * Define a one-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ibuHamil()
    {
        return $this->hasOne(IbuHamil::class, 'kia_id');
    }

    /**
     * Define a one-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function bulananAnak()
    {
        return $this->hasOne(Anak::class, 'kia_id');
    }
}
