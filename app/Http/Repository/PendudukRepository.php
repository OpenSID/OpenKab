<?php

namespace App\Http\Repository;

use App\Models\Penduduk;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class PendudukRepository
{
    public function listPenduduk()
    {
        return QueryBuilder::for(Penduduk::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('nik'),
                'nama',
            ])
            ->allowedSorts([
                'nama',
            ])
            ->jsonPaginate();
    }
}