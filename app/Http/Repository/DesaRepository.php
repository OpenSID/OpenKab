<?php

namespace App\Http\Repository;

use App\Models\Config;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DesaRepository
{
    public function list()
    {
        return  QueryBuilder::for(Config::with('sebutanDesa'))
            ->allowedFilters([
                AllowedFilter::exact('kode_kecamatan'),
                AllowedFilter::exact('kode_desa'),
                AllowedFilter::exact('id'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->orWhere('nama_desa', 'like', "%{$value}%");
                        $query->orWhere('kode_desa', 'like', "%{$value}%");
                        $query->orWhere('website', 'like', "%{$value}%");
                    });
                }),
            ])->allowedSorts([
                'kode_kecamatan',
                'kode_desa',
                'nama_desa',
                'website',
            ])
            ->allowedFields([
                'id',
                'kode_kecamatan',
                'nama_kecamatan',
                'kode_pos',
                'kode_desa',
                'nama_desa',
                'website',
                'path',
                'lat',
                'lng',
            ])
            ->jsonPaginate();
    }

    public function all($kec = '')
    {
        // Inisialisasi query
        $query = Config::with('sebutanDesa');

        // Tambahkan filter berdasarkan kode_kecamatan
        if ($kec) {
            $query->where('kode_kecamatan', $kec);
        }

    // Eksekusi query dan kembalikan hasilnya
        return $query->get(); // Mengambil semua data tanpa pagination
    }
}
