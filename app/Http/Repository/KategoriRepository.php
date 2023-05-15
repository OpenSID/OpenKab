<?php

namespace App\Http\Repository;

use App\Models\Artikel;
use App\Models\Kategori;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class KategoriRepository
{
    public function listKategori()
    {
        return QueryBuilder::for(Kategori::class)
            ->select('*')
            ->addSelect([
                'jml_artikel' => Artikel::selectRaw('count(artikel.id_kategori)')
                    ->whereRaw('artikel.id_kategori = kategori.id')
                    ->orWhereRaw('artikel.id_kategori IN (SELECT a.id FROM kategori AS a WHERE a.parrent = kategori.id)'),
            ])
            ->whereNull('config_id')
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('parrent'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('kategori', 'LIKE', '%'.$value.'%');
                }),
                'kategori',
                'tipe',
            ])
            ->allowedSorts([
                'kategori',
                'tipe',
                'id',
                'parrent',
            ])
            ->jsonPaginate();
    }

    public function show($id)
    {
        return Kategori::where('id', $id)->whereNull('config_id')->first();
    }
}
