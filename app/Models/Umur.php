<?php

namespace App\Models;

use App\Models\Enums\JenisKelaminEnum;
use App\Models\Traits\ConfigIdTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Umur extends BaseModel
{
    use ConfigIdTrait;

    public const RENTANG = 1;

    public const KATEGORI = 0;

    private $klasifikasi;

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
        // $where = "AND (DATE_FORMAT(FROM_DAYS(TO_DAYS( NOW()) - TO_DAYS(tanggallahir)) , '%Y')+0)>=dari AND (DATE_FORMAT(FROM_DAYS( TO_DAYS(NOW()) - TO_DAYS(tanggallahir)) , '%Y')+0) <= sampai AND akta_lahir <> ''";

        return $this->scopeCountStatistik($query, 'akta');
    }

    /**
     * Scope untuk Statistik Umur Rentang dan Kategori.
     */
    public function scopecountStatistikUmur($query)
    {
        // $where = "AND (DATE_FORMAT(FROM_DAYS(TO_DAYS( NOW()) - TO_DAYS(tanggallahir)) , '%Y')+0)>=dari AND (DATE_FORMAT(FROM_DAYS( TO_DAYS(NOW()) - TO_DAYS(tanggallahir)) , '%Y')+0) <= sampai";
        return $this->scopeCountStatistik($query, 'umur');
    }

    /**
     * Scope untuk Statistik.
     */
    public function scopeCountStatistik($query, $type = '')
    {
        $defaultConfigId = 1;
        $configDesa = null;
        if (session()->has('desa')) {
            // $where .= ' AND tweb_penduduk.config_id = '.session('desa.id');
            $query = $this->scopeConfigId($query);
            // $defaultConfigId = session('desa.id');
            $configDesa = session('desa.id');
        }
        if (request('config_desa')) {
            // $defaultConfigId = request('config_desa');
            $configDesa = request('config_desa');
        }

        $query = $query->where('config_id', $defaultConfigId);

        $tanggalPeristiwa = null;
        if (isset(request('filter')['tahun']) || isset(request('filter')['bulan'])) {
            $periode = [request('filter')['tahun'] ?? date('Y'), request('filter')['bulan'] ?? '12', '01'];
            $tanggalPeristiwa = Carbon::parse(implode('-', $periode))->endOfMonth()->format('Y-m-d');
        }
        $logPenduduk = LogPenduduk::select(['log_penduduk.id_pend'])->peristiwaTerakhir($tanggalPeristiwa, $configDesa)->tidakMati()->toBoundSql();
        $queryPenduduk = Penduduk::selectRaw("config_id, sex, (DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW()) - TO_DAYS(tanggallahir)),'%Y') + 0) as umur")
                ->join(DB::raw("($logPenduduk) as log"), 'log.id_pend', '=', 'tweb_penduduk.id')
                ->when($configDesa, function ($q) use ($configDesa) {
                    return $q->where(['config_id' => $configDesa]);
                })->when($type == 'akta', function ($q) {
                    return $q->whereNotNull('akta_lahir');
                })->toBoundSql();
        $subQuery = "select  tpu.id, count(tp.umur) as total, sex  from ($queryPenduduk) as tp
            join tweb_penduduk_umur tpu on tpu.config_id = $defaultConfigId and (tp.umur BETWEEN tpu.dari and tpu.sampai) and tpu.status = ".$this->getKlasifikasi().'
            group by tpu.id, tp.sex';

        $lk = JenisKelaminEnum::laki_laki;
        $pr = JenisKelaminEnum::perempuan;
        $newQuery = $query
            ->select(['tweb_penduduk_umur.id', 'nama'])
            ->selectRaw("sum(case when x.sex = $lk then x.total else 0 end) as laki_laki")
            ->selectRaw("sum(case when x.sex = $pr then x.total else 0 end) as perempuan")
            ->leftJoin(DB::raw("($subQuery) as x"), 'x.id', '=', 'tweb_penduduk_umur.id')
            ->groupBy(['tweb_penduduk_umur.id', 'nama'])
            ->orderBy('tweb_penduduk_umur.dari');
        // ->selectRaw("(SELECT COUNT(tweb_penduduk.id) FROM tweb_penduduk $joinLogStr WHERE tweb_penduduk.`sex` = '$lk' AND tweb_penduduk.`status_dasar` = 1 $where) as laki_laki")
        // ->selectRaw("(SELECT COUNT(tweb_penduduk.id) FROM tweb_penduduk $joinLogStr WHERE tweb_penduduk.`sex` = '$pr' AND tweb_penduduk.`status_dasar` = 1 $where) as perempuan");

        return $newQuery;
    }

    /**
     * Get the value of klasifikasi.
     */
    public function getKlasifikasi()
    {
        return $this->klasifikasi ?? self::RENTANG;
    }

    /**
     * Set the value of klasifikasi.
     *
     * @return self
     */
    public function setKlasifikasi($klasifikasi)
    {
        $this->klasifikasi = $klasifikasi;

        return $this;
    }
}

// select tpu.nama,
// 	sum(case when x.sex = 1 then x.total else 0 end) as 'laki-laki',
// 	sum(case when x.sex = 2 then x.total else 0 end) as 'perempuan'
// from  tweb_penduduk_umur tpu
// left join (
//     select  tpu.id, count(tp.umur) as total, sex  from (
//     select config_id, sex, (DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW()) - TO_DAYS(tanggallahir)),'%Y') + 0) as umur
//     from tweb_penduduk
//     ) tp join tweb_penduduk_umur tpu on tpu.config_id = 1 and (tp.umur BETWEEN tpu.dari and tpu.sampai) and tpu.status = 1
//     group by tpu.id, tp.sex
// )x on x.id = tpu.id
// where tpu.config_id = 1 and tpu.status = 1
// group by tpu.nama
// order by tpu.dari
