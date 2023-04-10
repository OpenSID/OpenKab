<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /**
     * Select untuk Statistik menggunakan case
     */
    public function selectCountStatistikWithCase($query)
    {
        return $query
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan');
    }

    /**
     * Select untuk Statistik menggunakan subquery
     */
    public function selectCountStatistikWithSubQuery($query, $where = null, $umur = false)
    {
        if ($umur) {
            $where = "AND (DATE_FORMAT(FROM_DAYS(TO_DAYS( NOW()) - TO_DAYS(tanggallahir)) , '%Y')+0)>=dari AND (DATE_FORMAT(FROM_DAYS( TO_DAYS(NOW()) - TO_DAYS(tanggallahir)) , '%Y')+0) <= sampai $where";
        }
        return $query
            ->selectRaw("(SELECT COUNT(tweb_penduduk.id) FROM tweb_penduduk WHERE tweb_penduduk.`sex` = '1' AND tweb_penduduk.`status_dasar` = 1 $where) as laki_laki")
            ->selectRaw("(SELECT COUNT(tweb_penduduk.id) FROM tweb_penduduk WHERE tweb_penduduk.`sex` = '2' AND tweb_penduduk.`status_dasar` = 1 $where) as perempuan");
    }
}
