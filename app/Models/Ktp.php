<?php

namespace App\Models;

use App\Models\Traits\ConfigIdTrait;

class Ktp extends BaseModel
{
    use ConfigIdTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tweb_status_ktp';

    /**
     * Scope untuk Statistik Hamil.
     */
    public function scopeCountStatistik($query)
    {
        $where = "((DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW()) - TO_DAYS(tanggallahir)), '%Y')+0)>=17 OR (status_kawin IS NOT NULL AND status_kawin <> 1)) AND status_rekam = isi_status_rekam ";

        return $query
            ->select('id', 'nama', 'status_rekam AS isi_status_rekam')
            ->selectRaw("(SELECT COUNT(tweb_penduduk.id) FROM tweb_penduduk WHERE tweb_penduduk.`sex` = '1' AND tweb_penduduk.`status_dasar` = 1 AND $where) as laki_laki")
            ->selectRaw("(SELECT COUNT(tweb_penduduk.id) FROM tweb_penduduk WHERE tweb_penduduk.`sex` = '2' AND tweb_penduduk.`status_dasar` = 1 AND $where) as perempuan");
    }
}
