<?php

namespace App\Http\Repository;

use App\Models\Config;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ArtikelRepository
{
    public function listArtikel()
    {
        return [
            'statistik_berita' => $this->statistikBerita(),
        ];
    }

    private function statistikBerita()
    {
        $query = Config::query()
            ->select('nama_desa')
            ->selectRaw('count(artikel.id) as jumlah')
            ->join('artikel', 'config.id', '=', 'artikel.config_id')
            ->groupBy('config.id');

        return QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::exact('nama_desa'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('nama_desa', 'LIKE', '%'.$value.'%')
                        ->orWhere('tgl_upload', 'LIKE', '%'.$value.'%');
                }),
                AllowedFilter::callback('tahun', function ($query, $value) {
                    $query->whereYear('tgl_upload', '<=', $value)
                        ->whereYear('tgl_upload', '>=', $value);
                }),
                AllowedFilter::callback('bulan', function ($query, $value) {
                    $query->whereMonth('tgl_upload', '<=', $value)
                        ->whereMonth('tgl_upload', '>=', $value);
                }),
                AllowedFilter::callback('id_kategori', function ($query, $value) {
                    $query->where('id_kategori', $value);
                }),

            ])
            ->allowedSorts([
                'nik',
                'nama',
                'umur',
                'created_at',
            ])
            ->jsonPaginate();
    }
}
