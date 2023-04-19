<?php

namespace App\Models;

use App\Models\Traits\ConfigIdTrait;
use Illuminate\Database\Eloquent\Model;

class Umur extends BaseModel
{
    use ConfigIdTrait;

    public const RENTANG = 1;

    public const KATEGORI = 0;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tweb_penduduk_umur';

    /**
     * Scope status umur.
     */
    public function scopeStatus($query, $status = self::RENTANG)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk Statistik Akta Kelahiran.
     */
    public function scopecountStatistikAkta($query)
    {
        $where = "AND (DATE_FORMAT(FROM_DAYS(TO_DAYS( NOW()) - TO_DAYS(tanggallahir)) , '%Y')+0)>=dari AND (DATE_FORMAT(FROM_DAYS( TO_DAYS(NOW()) - TO_DAYS(tanggallahir)) , '%Y')+0) <= sampai AND akta_lahir <> ''";

        return $this->scopeCountStatistik($query, $where);
    }

    /**
     * Scope untuk Statistik Umur Rentang dan Kategori.
     */
    public function scopecountStatistikUmur($query)
    {
        $where = "AND (DATE_FORMAT(FROM_DAYS(TO_DAYS( NOW()) - TO_DAYS(tanggallahir)) , '%Y')+0)>=dari AND (DATE_FORMAT(FROM_DAYS( TO_DAYS(NOW()) - TO_DAYS(tanggallahir)) , '%Y')+0) <= sampai";

        return $this->scopeCountStatistik($query, $where);
    }

    /**
     * Scope untuk Statistik.
     */
    public function scopeCountStatistik($query, $where = '')
    {
        if (session()->has('desa')) {
            $where .= ' AND tweb_penduduk.config_id = '.session('desa.id');
            $query = $this->scopeConfigId($query);
        } else {
            $query = $query->where('config_id', 1);
        }

        $newQuery = $query
            ->select(['id', 'nama'])
            ->selectRaw("(SELECT COUNT(tweb_penduduk.id) FROM tweb_penduduk WHERE tweb_penduduk.`sex` = '1' AND tweb_penduduk.`status_dasar` = 1 $where) as laki_laki")
            ->selectRaw("(SELECT COUNT(tweb_penduduk.id) FROM tweb_penduduk WHERE tweb_penduduk.`sex` = '2' AND tweb_penduduk.`status_dasar` = 1 $where) as perempuan");
            // ->when(session()->has('desa'), function ($query) {
            //     return $query->grubBy("{$this->table}.nama");
            // })
            // ->groupBy("{$this->table}.nama")

        return $newQuery;
    }
}
