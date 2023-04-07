<?php

namespace App\Http\Repository;

use App\Models\KeluargaSejahtera;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class KeluargaRepository
{
    public function kelasSosial()
    {
        return QueryBuilder::for(KeluargaSejahtera::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'nama',
            ])
            ->allowedSorts([
                'nama',
            ])
            ->get();
    }
}
