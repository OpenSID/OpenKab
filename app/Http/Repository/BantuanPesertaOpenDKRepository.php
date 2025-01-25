<?php

namespace App\Http\Repository;

use App\Models\BantuanPeserta;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BantuanPesertaOpenDKRepository
{
    public function listBantuanPesertaSyncOpenDk($all = false)
    {
        $bantuan = QueryBuilder::for(BantuanPeserta::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('program_id'),
                AllowedFilter::exact('peserta'),
                AllowedFilter::callback('kode_kecamatan', function ($query, $value) {
                    $query->whereHas('config', function ($query) use ($value) {
                        $query->where('kode_kecamatan', $value);
                    });
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('no_id_kartu', 'LIKE', '%'.$value.'%')
                        ->orWhere('kartu_nama', 'LIKE', '%'.$value.'%');
                }),
            ])
            ->allowedSorts([
                'no_id_kartu',
                'kartu_nama',
            ]);

        if ($all) {
            return $bantuan->jsonPaginate();
        }

        return $bantuan->get();
    }

    public function getBantuanPeserta($all, $id, $kode_desa)
    {
        $bantuan = QueryBuilder::for(BantuanPeserta::class)
            ->where('program_id', $id)
            ->whereHas('config', function ($query) use ($kode_desa) {
                $query->where('kode_desa', $kode_desa);
            })
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('program_id'),
                AllowedFilter::exact('peserta'),
                AllowedFilter::callback('kode_kecamatan', function ($query, $value) {
                    $query->whereHas('config', function ($query) use ($value) {
                        $query->where('kode_kecamatan', $value);
                    });
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->Where('kartu_nama', 'LIKE', '%'.$value.'%');
                }),
            ])
            ->allowedSorts([
                'peserta',
                'no_id_kartu',
                'kartu_nik',
                'kartu_nama',
                'kartu_tempat_lahir',
                'kartu_tanggal_lahir',
                'kartu_alamat',
            ]);

        if ($all) {
            return $bantuan->jsonPaginate();
        }

        return $bantuan->get();
    }
}
