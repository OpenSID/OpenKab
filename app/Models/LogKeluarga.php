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
    public $timestamps = false;

    protected $fillable = ['config_id', 'id_kk', 'id_peristiwa', 'id_pend', 'updated_by', 'id_log_penduduk'];

}
