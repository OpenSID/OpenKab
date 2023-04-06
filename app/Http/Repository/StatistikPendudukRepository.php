<?php

namespace App\Http\Repository;

use App\Models\Bantuan;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class StatistikPendudukRepository
{
    public function listBantuan()
    {
        return QueryBuilder::for(Bantuan::class)
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
