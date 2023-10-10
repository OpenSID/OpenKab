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

    const KELUARGA_BARU = 1;

    const KEPALA_KELUARGA_MATI          = 2;
    const KEPALA_KELUARGA_PINDAH        = 3;
    const KEPALA_KELUARGA_HILANG        = 4;
    const KELUARGA_BARU_DATANG          = 5;
    const KEPALA_KELUARGA_PERGI         = 6;
    const KEPALA_KELUARGA_TIDAK_VALID   = 11;
    const ANGGOTA_KELUARGA_PECAH        = 12;
    const KELUARGA_HAPUS                = 13;
    const KEPALA_KELUARGA_KEMBALI_HIDUP = 14;
}
