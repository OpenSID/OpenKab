<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Suplemen extends BaseModel
{
    use HasFactory;

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

    public function terdata()
    {
        return $this->hasMany(SuplemenTerdata::class, 'id_suplemen');
    }
}
