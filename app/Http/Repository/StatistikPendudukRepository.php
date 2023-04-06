<?php

namespace App\Http\Repository;

use App\Models\LaporanPenduduk;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class StatistikPendudukRepository
{
    public function listPenduduk()
    {
        return QueryBuilder::for(LaporanPenduduk::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'nama',
                'sasaran',
            ])
            ->allowedSorts([
                'nama',
                'sasaran',
            ])
            ->jsonPaginate();
    }
}
