<?php

namespace App\Http\Repository;

use App\Models\Penduduk;
use App\Models\Potensi;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class KelembagaanRepository
{
    public function index()
    {
        return QueryBuilder::for(Potensi::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('kategori'),
            ])->where('kategori', 'lembaga-adat')
            ->jsonPaginate();
    }

    public function penduduk($config_id)
    {
        return QueryBuilder::for(Penduduk::filterWilayah())
            ->with('agama')
            ->select(['nik', 'agama_id', 'suku'])
            ->where('config_id', $config_id)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('sex'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('status_dasar'),
                AllowedFilter::exact('keluarga.no_kk'),
                AllowedFilter::exact('clusterDesa.dusun'),
                AllowedFilter::exact('clusterDesa.rw'),
                AllowedFilter::exact('clusterDesa.rt'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('nama', 'like', "%{$value}%")
                            ->orWhere('nik', 'like', "%{$value}%")
                            ->orWhere('tag_id_card', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts([
                'nik',
                'nama',
                'umur',
                'created_at',
            ])
            ->jsonPaginate();
    }
}
