<?php

namespace App\Models;

use App\Models\Traits\ConfigIdTrait;
use Illuminate\Database\Eloquent\Model;

class Hamil extends Model
{
    use ConfigIdTrait;

    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ref_penduduk_hamil';

    /**
     * Scope untuk Statistik Hamil
     */
    public function scopeCountStatistik($query)
    {
        $where = "hamil = 1 AND sex = 2";
        return $query
            ->select(['id', 'nama'])
            ->selectRaw("(SELECT COUNT(tweb_penduduk.id) FROM tweb_penduduk WHERE tweb_penduduk.`sex` = '1' AND $where) as laki_laki")
            ->selectRaw("(SELECT COUNT(tweb_penduduk.id) FROM tweb_penduduk WHERE tweb_penduduk.`sex` = '2' AND $where) as perempuan");
    }
}
