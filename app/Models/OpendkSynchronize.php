<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpendkSynchronize extends Model
{
    use HasFactory;

    /** {@inheritdoc} */
    protected $table = 'opendk_synchronize';

    /**
     * The fillable with the model.
     *
     * @var array
     */
    protected $fillable = [
        'kode_kecamatan',
        'nama_kecamatan',
    ];
}
