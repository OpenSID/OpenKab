<?php

namespace App\Http\Repository;

use App\Models\Penduduk;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class PendudukRepository
{
    public function listPenduduk()
    {
        return QueryBuilder::for(Penduduk::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('keluarga.no_kk'),
                'nama',
                'nik',
                'tag_id_card',
            ])
            ->allowedSorts([
                'nik',
                'nama',
                'umur',
                'created_at',
            ])
            ->jsonPaginate();
    }

    public function listStatistik()
    {
        $rtm = Umur::countStatistik();

        $jumlah = $rtm->bdt(true)->get();
        $jumlah_laki_laki = $jumlah->sum('laki_laki');
        $jumlah_perempuan = $jumlah->sum('perempuan');
        $jumlah = $jumlah_laki_laki + $jumlah_perempuan;

        $total  = $rtm->get();
        $total_laki_laki = $total->sum('laki_laki');
        $total_perempuan = $total->sum('perempuan');
        $total = $total_laki_laki + $total_perempuan;

        return [
            [
                'nama' => 'Jumlah',
                'jumlah' => $jumlah,
                'laki_laki' => $jumlah_laki_laki,
                'perempuan' => $jumlah_perempuan,
            ],
            [
                'nama' => 'Belum Mengisi',
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
