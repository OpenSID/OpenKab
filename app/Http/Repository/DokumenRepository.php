<?php

namespace App\Http\Repository;

use App\Models\DokumenHidup;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DokumenRepository
{
    public function listDokumen()
    {
        return QueryBuilder::for(DokumenHidup::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('id_pend'),
            ])
            ->jsonPaginate();
    }
}
