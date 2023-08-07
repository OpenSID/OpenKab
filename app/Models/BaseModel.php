<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BaseModel extends Model
{
    use LogsActivity;
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $dbConnection;

    protected $guarded = [];

    /**
     * constract.
     */
    public function __construct()
    {
        parent::__construct();
        $this->dbConnection = DB::connection($this->connection);
    }

    /**
     * Select untuk Statistik menggunakan case.
     */
    public function selectCountStatistikWithCase($query)
    {
        return $query
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan');
    }

    /**
     * Select untuk Statistik menggunakan subquery.
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

    public function scopeWhereRaws($query, $where)
    {
        return $query->when($where, function ($query) use ($where) {
            $query->whereRaw($where);
        });
    }

    public function scopeConfigId($query)
    {
        return $query->when(session()->has('desa'), function ($query) {
            $query->where("{$this->table}.config_id", session('desa.id'));
        });
    }

    public function scopeFilters($query, array $filters = [], $column = self::CREATED_AT)
    {
        return $query->when($filters['tahun'], function ($query) use ($filters, $column) {
            $query->whereYear($column, '<=', $filters['tahun'])
                ->when($filters['bulan'], function ($query) use ($filters, $column) {
                    $query->whereMonth($column, '<=', $filters['bulan']);
                });
        });
    }

    public function scopeMinMaxTahun($query, $column = self::CREATED_AT)
    {
        return $query->selectRaw("YEAR(MIN({$column})) AS tahun_awal, YEAR(MAX({$column})) AS tahun_akhir");
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->useLogName('data-log');;
        // Chain fluent methods for configuration options
    }
}
