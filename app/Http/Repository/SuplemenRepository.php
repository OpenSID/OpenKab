<?php

namespace App\Http\Repository;

use App\Models\Suplemen;
use App\Models\SuplemenTerdata;
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

    public function listSuplemenTerdata($sasaran, $id)
    {
        return  QueryBuilder::for(SuplemenTerdata::anggota($sasaran, $id))
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('sex'),
                AllowedFilter::exact('dusun'),
                AllowedFilter::exact('rw'),
                AllowedFilter::exact('rt'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('keterangan', 'LIKE', '%'.$value.'%');
                }),

            ])
            ->allowedSorts([
                'keterangan',
            ])->jsonPaginate();
    }
}
