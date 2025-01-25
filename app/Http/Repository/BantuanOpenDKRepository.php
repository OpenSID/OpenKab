<?php

namespace App\Http\Repository;

use App\Models\Bantuan;
use App\Models\Kelompok;
use App\Models\Keluarga;
use App\Models\Rtm;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BantuanOpenDKRepository
{
    public const SASARAN_PENDUDUK = 1;

    public const SASARAN_KELUARGA = 2;

    public const SASARAN_RUMAH_TANGGA = 3;

    public const SASARAN_KELOMPOK = 4;

    public function listBantuanSyncOpenDk()
    {
        return  QueryBuilder::for(Bantuan::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('sasaran'),
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
                    $query->where('nama', 'LIKE', '%'.$value.'%')
                        ->orWhere('asaldana', 'LIKE', '%'.$value.'%');
                }),
                AllowedFilter::callback('tahun', function ($query, $value) {
                    $query->whereYear('sdate', '<=', $value)
                        ->whereYear('edate', '>=', $value);
                }),

            ])
            ->allowedSorts([
                'nama',
                'asaldana',
            ])->jsonPaginate();
    }

    public function getBantuan($id)
    {
        return  QueryBuilder::for(Bantuan::class)
            ->where('id', $id)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('sasaran'),
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
                    $query->where('nama', 'LIKE', '%'.$value.'%')
                        ->orWhere('asaldana', 'LIKE', '%'.$value.'%');
                }),
                AllowedFilter::callback('tahun', function ($query, $value) {
                    $query->whereYear('sdate', '<=', $value)
                        ->whereYear('edate', '>=', $value);
                }),

            ])->first();
    }
}
