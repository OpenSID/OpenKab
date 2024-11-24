<?php

namespace App\Http\Repository;

use App\Models\Komoditas;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PariwisataRepository
{
    public function listPariwisataKomoditas()
    {
        return QueryBuilder::for(Komoditas::filterWilayah())
            ->from('prodeskel_komoditas as pk')
            ->whereIn('kategori', ['sarana-wisata', 'potensi-wisata'])
            ->leftJoin('config as c', 'c.id', 'pk.config_id')
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('pk.kategori', 'like', "%{$value}%")
                            ->orWhere('pk.komoditas', 'like', "%{$value}%")
                            ->orWhere('c.kode_desa', 'like', "%{$value}%")
                            ->orWhere('c.nama_desa', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts([
                'c.nama_desa',
                'c.kode_desa',
                'pk.komoditas',
                'pk.created_at',
            ])
            ->jsonPaginate();
    }
}
