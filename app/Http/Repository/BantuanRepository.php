<?php

namespace App\Http\Repository;

use App\Models\Bantuan;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class BantuanRepository
{
    public function listBantuan()
    {
        $query = Bantuan::query();

        if (session()->has('desa')) {
            $query->where('config_id', session('desa.id'));
        }

        return QueryBuilder::for($query)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'nama',
                'sasaran',
            ])
            ->allowedSorts([
                'nama',
                'sasaran',
            ])
            ->jsonPaginate();
    }

    public function showBantuan()
    {
        $query = Bantuan::query();

        if (session()->has('desa')) {
            $query->where('config_id', session('desa.id'));
        }

        return QueryBuilder::for($query)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'nama',
                'sasaran',
            ])
            ->allowedSorts([
                'nama',
                'sasaran',
            ])
            ->first();
    }

    public function listStatistik()
    {
        $query = Bantuan::query();

        if (session()->has('desa')) {
            $query->where('config_id', session('desa.id'));
        }

        $result = QueryBuilder::for($query)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'nama',
                'sasaran',
            ])
            ->first();

        $jumlah = $result->statistik[0];
        $jumlah_laki_laki = $jumlah['laki_laki'];
        $jumlah_perempuan = $jumlah['perempuan'];
        $jumlah = $jumlah_laki_laki + $jumlah_perempuan;

        $total  = $result->statistik[1];
        $total_laki_laki = $total['laki_laki'];
        $total_perempuan = $total['perempuan'];
        $total = $total_laki_laki + $total_perempuan;

        return [
            [
                'nama' => 'Peserta',
                'jumlah' => $jumlah,
                'laki_laki' => $jumlah_laki_laki,
                'perempuan' => $jumlah_perempuan,
            ],
            [
                'nama' => 'Bukan Peserta',
                'jumlah' => 0,
                'laki_laki' => 0,
                'perempuan' => 0,
            ],
            [
                'nama' => 'Total',
                'jumlah' => $total,
                'laki_laki' => $total_laki_laki,
                'perempuan' => $total_perempuan,
            ],
        ];
    }
}
