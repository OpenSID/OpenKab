<?php

namespace App\Http\Repository;

use App\Models\Berita;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class BeritaRepository
{
    public function listBerita()
    {
        return QueryBuilder::for(Berita::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('config_id'),
                AllowedFilter::exact('id_kategori'),
                'bulan',
                'tahun',
                'tgl_upload',
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('tgl_upload', 'LIKE', '%' . $value . '%');
                }),
            ])
            ->allowedSorts(['tgl_upload'])
            ->jsonPaginate();
    }
}
