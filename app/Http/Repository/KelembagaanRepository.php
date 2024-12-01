<?php

namespace App\Http\Repository;

use App\Models\Potensi;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

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
