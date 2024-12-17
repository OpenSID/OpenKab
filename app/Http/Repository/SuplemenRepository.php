<?php

namespace App\Http\Repository;

use App\Models\Suplemen;
use App\Models\SuplemenTerdata;
use App\Models\Wilayah;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SuplemenRepository
{
    public function listSuplemen()
    {
        return  QueryBuilder::for(Suplemen::withCount('terdata'))
            ->where('sumber', 'OpenKab')
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('sasaran'),
                AllowedFilter::exact('status'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('nama', 'LIKE', '%'.$value.'%');
                }),

            ])
            ->allowedSorts([
                'nama',
                'keterangan',
                'sasaran',
            ])->jsonPaginate();
    }

    public function listSuplemenTerdata($sasaran, $id)
    {
        return  QueryBuilder::for(SuplemenTerdata::anggota($sasaran, $id))
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('tweb_penduduk.sex'),
                AllowedFilter::callback('tweb_wil_clusterdesa.dusun', function ($query, $value) {
                    // Asumsi $value adalah ID dusun yang dikirimkan
                    $dusun = Wilayah::find($value);
                    if ($dusun) {
                        $query->where('tweb_wil_clusterdesa.dusun', $dusun->dusun);
                    }
                }),
                AllowedFilter::exact('tweb_wil_clusterdesa.rw'),
                AllowedFilter::exact('tweb_wil_clusterdesa.rt'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('keterangan', 'LIKE', '%'.$value.'%');
                }),

            ])
            ->allowedSorts([
                'keterangan',
            ])->jsonPaginate();
    }
}
