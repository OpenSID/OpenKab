<?php

namespace App\Http\Repository;

use App\Models\Rtm;
use App\Models\Bantuan;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class RtmRepository
{
    public function listStatistik()
    {
        $rtm = Rtm::countStatistik();

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
