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
                AllowedFilter::callback('search', fn ($query, $search) => $query->where('no_kk', 'like', "%$search%")),
            ])
            ->without('wilayah')
            ->with([
                'prodeskelDDK',
                'prodeskelDDK.produksi',
                'prodeskelDDK.detail',
                'prodeskelDDK.bahanGalianAnggota',
            ])
            ->whereHas('prodeskelDDK', fn ($query) => $query->with(['produksi', 'detail', 'bahanGalianAnggota']))
            ->status()
            ->filterWilayah()
            ->jsonPaginate();
    }
}
