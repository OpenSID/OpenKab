<?php

namespace App\Http\Repository;

use App\Models\BantuanPeserta;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\Bantuan;

class BantuanPesertaRepository
{
    public function listBantuanPeserta()
    {
        $query = BantuanPeserta::configId();

        return QueryBuilder::for($query)
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
