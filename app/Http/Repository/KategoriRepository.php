<?php

namespace App\Http\Repository;

use App\Models\Kategori;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class KategoriRepository
{
    public function listKategori()
    {
        return QueryBuilder::for(Kategori::class)
            ->whereNull('config_id')
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'kategori',
                'parrent',
                'tipe',
                'parrent',
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
        return Kategori::where('id', $id)->whereNull('config_id')
        ->first();
    }
}
