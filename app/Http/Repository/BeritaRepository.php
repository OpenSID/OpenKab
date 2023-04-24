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
            ->jsonPaginate();
    }
}
