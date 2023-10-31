<?php

namespace App\Models;

use App\Models\Enums\StatusDasarEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LogPenduduk extends Model
{
    use HasFactory;

    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /**
     * {@inheritdoc}
     */
    protected $table = 'log_penduduk';

    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    const BARU_LAHIR = 1;

    const MATI = 2;

    const PINDAH_KELUAR = 3;

    const HILANG = 4;

    const BARU_PINDAH_MASUK = 5;

    const TIDAK_TETAP_PERGI = 6;

    const PERISTIWA = [1, 2, 3, 4];

    public function scopeTahun($query)
    {
        return $query->selectRaw('YEAR(MIN(tgl_peristiwa)) AS tahun_awal, YEAR(MAX(tgl_peristiwa)) AS tahun_akhir');
    }

    public function scopeTidakMati($query)
    {
        return $query->where('kode_peristiwa', '!=', StatusDasarEnum::MATI);
    }

    public function scopePeristiwaSampai($query, $tanggal)
    {
        return $query->where('tgl_peristiwa', '<=', $tanggal);
    }

    public function scopePeristiwaTerakhir($query, $tanggal = null, $configId = null)
    {
        if (! empty($tanggal)) {
            $query->where('tgl_peristiwa', '<=', $tanggal);
        }

        if (! empty($configId)) {
            $query->where('config_id', $configId);
        }

        $subQuery = DB::raw(
            '(SELECT MAX(id) as id, id_pend from log_penduduk group by id_pend) as logMax'
        );

        if (! empty($configId)) {
            $subQuery = DB::raw(
                '(SELECT MAX(id) as id, id_pend from log_penduduk where config_id = '.$configId.' group by id_pend) as logMax'
            );
        }

        return $query->join($subQuery, 'logMax.id', '=', 'log_penduduk.id');
    }
}
