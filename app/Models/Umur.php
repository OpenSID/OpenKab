<?php

namespace App\Models;

use App\Models\Traits\ConfigIdTrait;
use Illuminate\Database\Eloquent\Model;

class Umur extends Model
{
    use ConfigIdTrait;

    public const RENTANG = 1;
    public const KATEGORI = 0;

    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tweb_penduduk_umur';

    /**
     * Scope status umur
     */
    public function scopeStatus($query, $status = self::RENTANG)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope lainya untuk where
     */
    public function scopeCountAktaStatistik($query)
    {
        return $this->scopeCountStatistik($query, " AND akta_lahir <> '' ");
    }

    public function scopeCountStatistik($query, $whereElse)
    {
        $where = "(DATE_FORMAT(FROM_DAYS(TO_DAYS( NOW()) - TO_DAYS(tanggallahir)) , '%Y')+0)>=dari AND (DATE_FORMAT(FROM_DAYS( TO_DAYS(NOW()) - TO_DAYS(tanggallahir)) , '%Y')+0) <= sampai";
        return $query
            ->select(['id', 'nama'])
            ->selectRaw("(SELECT COUNT(tweb_penduduk.id) FROM tweb_penduduk WHERE tweb_penduduk.`sex` = '1' AND $where) as laki_laki")
            ->selectRaw("(SELECT COUNT(tweb_penduduk.id) FROM tweb_penduduk WHERE tweb_penduduk.`sex` = '2' AND $where) as perempuan");
    }
}
