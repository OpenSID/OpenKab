<?php

namespace App\Http\Repository;

use App\Models\Pembangunan;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PembangunanOpenDKRepository
{
    public function listPembangunanSyncOpenDk()
    {
        return  QueryBuilder::for(Pembangunan::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::callback('kode_kecamatan', function ($query, $value) {
                    $query->whereHas('config', function ($query) use ($value) {
                        $query->where('kode_kecamatan', $value);
                    });
                }),
                AllowedFilter::callback('kode_desa', function ($query, $value) {
                    $query->whereHas('config', function ($query) use ($value) {
                        $query->where('kode_desa', $value);
                    });
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('judul', 'LIKE', '%'.$value.'%')
                        ->orWhere('sumber_dana', 'LIKE', '%'.$value.'%')
                        ->orWhere('anggaran', 'LIKE', '%'.$value.'%');
                }),

            ])
            ->allowedSorts([
                'judul',
                'sumber_dana',
                'anggaran',
            ])->jsonPaginate();
    }

    public function getPembangunan($id)
    {
        return  QueryBuilder::for(Pembangunan::class)
            ->where('id', $id)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::callback('kode_kecamatan', function ($query, $value) {
                    $query->whereHas('config', function ($query) use ($value) {
                        $query->where('kode_kecamatan', $value);
                    });
                }),
                AllowedFilter::callback('kode_desa', function ($query, $value) {
                    $query->whereHas('config', function ($query) use ($value) {
                        $query->where('kode_desa', $value);
                    });
                }),

            ])->first();
    }
}
