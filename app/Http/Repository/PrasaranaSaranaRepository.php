<?php

namespace App\Http\Repository;

use App\Models\Komoditas;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class PrasaranaSaranaRepository
{
    public function index()
    {
        return QueryBuilder::for(Komoditas::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('kategori'),
            ])
            ->jsonPaginate();
    }
}