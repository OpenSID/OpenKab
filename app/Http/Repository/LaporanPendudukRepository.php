<?php

namespace App\Http\Repository;

use App\Models\LaporanSinkronisasi;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class LaporanPendudukRepository
{
    public function listLaporanPenduduk()
    {
        return QueryBuilder::for(LaporanSinkronisasi::tipeLaporanPenduduk())
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::callback('kode_kecamatan', function ($query, $value) {
                    $query->whereHas('config', function ($query) use ($value) {
                        $query->where('kode_kecamatan', $value);
                    });
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('judul', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts(['id', 'tahun', 'semester'])
            ->jsonPaginate();
    }
}
