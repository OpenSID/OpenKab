<?php

namespace App\Http\Repository;

use App\Models\Potensi;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class KelembagaanRepository
{
    public function index()
    {
        return QueryBuilder::for(Potensi::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('kategori'),
            ])->where('kategori', 'lembaga-adat')
            ->jsonPaginate();
    }
}