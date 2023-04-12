<?php

namespace App\Http\Repository;

use App\Models\BantuanPeserta;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class BantuanPesertaRepository
{
    public function listBantuanPeserta()
    {
        return QueryBuilder::for(BantuanPeserta::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('program_id'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('no_id_kartu', 'LIKE', '%' . $value . '%')
                        ->orWhere('kartu_nama', 'LIKE', '%' . $value . '%');
                }),
            ])
            ->allowedSorts([
                'no_id_kartu',
                'kartu_nama',
            ])
            ->jsonPaginate();
    }
}
