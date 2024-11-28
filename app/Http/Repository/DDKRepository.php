<?php

namespace App\Http\Repository;

use App\Models\KeluargaDDK;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DDKRepository
{
    public function pangan()
    {
        return QueryBuilder::for(KeluargaDDK::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
            ])
            ->without('wilayah')
            ->with([
                'prodeskelDDK',
                'prodeskelDDK.produksi',
                'prodeskelDDK.detail',
                'prodeskelDDK.bahanGalianAnggota',
            ])
            ->status()
            ->jsonPaginate();
    }
}
