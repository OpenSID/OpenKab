<?php

namespace App\Http\Repository;

use App\Models\ClusterDesa;
use App\Models\Config;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class WilayahRepository
{
    public function listDesa()
    {
        return QueryBuilder::for(Config::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('nama_desa'),
                AllowedFilter::exact('kode_desa'),
                AllowedFilter::exact('nama_kecamatan'),
                AllowedFilter::callback('kode_kecamatan', function ($query, $value) {
                    $query->where('kode_kecamatan', '!=' , $value);
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('nama_desa', 'like', "%{$value}%");
                        $query->orWhere('nama_kecamatan', 'like', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('asal', function ($query, $value) {
                    $query->where('id', '!=' , $value);
                }),
            ])
            ->allowedSorts(['id', 'dusun'])

            ->whereRaw('nama_desa != ""')
            ->orderBy('kode_desa')
            ->jsonPaginate();
    }

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
