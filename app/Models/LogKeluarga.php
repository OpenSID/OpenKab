<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogKeluarga extends Model
{
    use HasFactory;

    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /**
     * {@inheritdoc}
     */
    protected $table = 'log_keluarga';

    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    public function scopeTahun($query)
    {
        return $query->selectRaw('YEAR(MIN(tgl_peristiwa)) AS tahun_awal, YEAR(MAX(tgl_peristiwa)) AS tahun_akhir');
    }
}
