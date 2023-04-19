<?php

namespace App\Http\Repository;

use App\Models\BantuanPeserta;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class BantuanPesertaRepository
{
    public function listBantuanPeserta($all= false)
    {
        $bantuan =  QueryBuilder::for(BantuanPeserta::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('program_id'),
                AllowedFilter::exact('peserta'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('no_id_kartu', 'LIKE', '%' . $value . '%')
                        ->orWhere('kartu_nama', 'LIKE', '%' . $value . '%');
                }),
            ])
            ->allowedSorts([
                'no_id_kartu',
                'kartu_nama',
            ]);
        if ($all) {
            return $bantuan->jsonPaginate();
        }else{
            return $bantuan->get();
        }

    }
}
