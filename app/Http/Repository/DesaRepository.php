<?php

namespace App\Http\Repository;

use App\Models\Config;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DesaRepository
{
    public function list()
    {
        return  QueryBuilder::for(Config::with('sebutanDesa'))
            ->allowedFilters([
                AllowedFilter::exact('kode_kecamatan'),
                AllowedFilter::exact('kode_desa'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('nama_desa', 'like', "%{$value}%");
                        $query->where('kode_desa', 'like', "%{$value}%");
                        $query->where('website', 'like', "%{$value}%");
                    });
                }),
            ])->allowedSorts([
                'kode_kecamatan',
                'kode_desa',
                'nama_desa',
                'website',
            ])
            ->allowedFields('*')
            ->jsonPaginate();
    }

    public function all()
{
    return Config::with('sebutanDesa')
        ->get();  // Hanya get() tanpa filter dan sorting
}
}
