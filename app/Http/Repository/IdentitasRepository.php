<?php

namespace App\Http\Repository;

use App\Models\Kategori;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IdentitasRepository
{
    public function identitas()
    {
        return QueryBuilder::for(Kategori::class)
            ->allowedFields('*')
            ->allowedFilters([
                'nama_aplikasi',
            ])
            ->allowedSorts([
                'nama_aplikasi',
            ])
            ->first();
    }
}
