<?php

namespace App\Http\Repository;

use App\Models\Kategori;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class KategoriRepository
{
    public function listKategori()
    {
        return QueryBuilder::for(Kategori::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'kategori',
                'parrent',
                'tipe',
                'parrent'
            ])
            ->allowedSorts([
                'kategori',
                'tipe',
                'id',
                'parrent',
            ])
            ->get();
    }

    public function show($id)
    {
        return Kategori::where('id', $id)
        ->first();
    }
}
