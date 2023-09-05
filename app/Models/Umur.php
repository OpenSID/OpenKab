<?php

namespace App\Models;

use App\Models\Traits\ConfigIdTrait;

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

        if (isset(request('filter')['tahun']) || isset(request('filter')['bulan'])) {
            $log_penduduk = LogPenduduk::select('log_penduduk.id')
            ->selectRaw('Max(log_penduduk.id) as max')
            ->where('kode_peristiwa', '!=', 2)
            ->whereRaw('tweb_penduduk.id = log_penduduk.id_pend')
            ->when(isset(request('filter')['tahun']), function ($q) {
                return $q->whereYear('tgl_peristiwa', '<=', request('filter')['tahun']);
            })
            ->when(isset(request('filter')['bulan']), function ($q) {
                return $q->whereMonth('tgl_peristiwa', '<=', request('filter')['bulan']);
            })
          ->groupBy('log_penduduk.id')
          ->toBoundSql();
        }

        if (isset($log_penduduk)) {
            $where .= " AND EXISTS($log_penduduk)";
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
