<?php

namespace App\Http\Repository;

use App\Models\Suplemen;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SuplemenRepository
{
    public function listSuplemen()
    {
        return  QueryBuilder::for(Suplemen::withCount('terdata'))
            ->where('sumber', 'OpenKab')
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('sasaran'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('nama', 'LIKE', '%'.$value.'%');
                }),

            ])
            ->allowedSorts([
                'nama',
                'keterangan',
                'sasaran',
            ])->jsonPaginate();
    }
}
