<?php

namespace App\Http\Repository;

use App\Models\KeluargaDDK;
use App\Models\Enums\StatusDasarEnum;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

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