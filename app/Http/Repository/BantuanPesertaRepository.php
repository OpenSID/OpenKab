<?php

namespace App\Http\Repository;

use App\Models\BantuanPeserta;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class BantuanPesertaRepository
{
    public function listBantuanPeserta()
    {
        $query = BantuanPeserta::query();

        if (session()->has('desa')) {
            $query->where('config_id', session('desa.id'));
        }

        return QueryBuilder::for($query)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'peserta',
                'program_id',
                'kartu_nik',
                'kartu_nama',
            ])
            ->allowedSorts([
                'peserta',
                'program_id',
                'kartu_nik',
                'kartu_nama',
            ])
            ->jsonPaginate();
    }
}
