<?php

namespace App\Http\Repository;

use App\Models\Komoditas;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PariwisataRepository
{
    public function listPariwisataKomoditas()
    {
        return QueryBuilder::for(Komoditas::class)
            ->whereIn('kategori', ['sarana-wisata', 'potensi-wisata'])
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('kategori', 'like', "%{$value}%")
                            ->orWhere('komoditas', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts([
                'komoditas',
                'created_at',
            ])
            ->jsonPaginate();
    }
}
