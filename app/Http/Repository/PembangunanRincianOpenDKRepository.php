<?php

namespace App\Http\Repository;

use App\Models\PembangunanRincian;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PembangunanRincianOpenDKRepository
{
    public function getPembangunanRincian($all, $id, $kode_desa)
    {
        $pembangunan = QueryBuilder::for(PembangunanRincian::class)
            ->where('id_pembangunan', $id)
            ->whereHas('config', function ($query) use ($kode_desa) {
                $query->where('kode_desa', $kode_desa);
            })
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('id_pembangunan'),
                AllowedFilter::callback('kode_kecamatan', function ($query, $value) {
                    $query->whereHas('config', function ($query) use ($value) {
                        $query->where('kode_kecamatan', $value);
                    });
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->Where('persentase', 'LIKE', '%'.$value.'%')
                        ->orWhere('keterangan', 'LIKE', '%'.$value.'%')
                        ->orWhere('created_at', 'LIKE', '%'.$value.'%');
                }),
            ])
            ->allowedSorts([
                'persentase',
                'keterangan',
                'created_at',
            ]);

        if ($all) {
            return $pembangunan->jsonPaginate();
        }

        return $pembangunan->get();
    }
}
