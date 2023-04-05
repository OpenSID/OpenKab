<?php

namespace App\Http\Repository;

use App\Models\Keluarga;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class KeluargaRepository
{
    public function listKeluarga()
    {
        return QueryBuilder::for(Keluarga::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'kelas_sosial',
            ])
            ->allowedSorts([
                'kelas_sosial',
            ])
            ->jsonPaginate();
    }
}
