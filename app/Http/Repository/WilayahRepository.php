<?php

namespace App\Http\Repository;

use App\Models\ClusterDesa;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class WilayahRepository
{
    public function listDusun()
    {
        return QueryBuilder::for(ClusterDesa::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('dusun', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts(['id', 'dusun'])
            ->dusun()
            ->orderBy('urut')
            ->jsonPaginate();
    }

    public function listRW()
    {
        return QueryBuilder::for(ClusterDesa::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::callback('subrw', function ($query, $value) {
                    $query->where('dusun', function ($query) use ($value) {
                        $query->from('tweb_wil_clusterdesa')->select('dusun')->where('id', $value);
                    });
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('rw', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts(['id', 'rw'])
            ->RW()
            ->orderBy('urut')
            ->jsonPaginate();
    }

    public function listRT()
    {
        return QueryBuilder::for(ClusterDesa::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('rw'),
                AllowedFilter::callback('subdusun', function ($query, $value) {
                    $query->where('dusun', function ($query) use ($value) {
                        $query->from('tweb_wil_clusterdesa')->select('dusun')->where('id', $value);
                    });
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('rt', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts(['id', 'rt'])
            ->RT()
            ->orderBy('urut')
            ->jsonPaginate();
    }
}