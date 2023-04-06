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

        $jumlah = $rtm->bdt()->first();
        $total  = $rtm->first();

        return [
            [
                'nama' => 'JUMLAH',
                'jumlah' => $jumlah->laki_laki + $jumlah->perempuan,
                'laki_laki' => $jumlah->laki_laki,
                'perempuan' => $jumlah->perempuan,
            ],
            [
                'nama' => 'BELUM MENGISI',
                'jumlah' => 0,
                'laki_laki' => 0,
                'perempuan' => 0,
            ],
            [
                'nama' => 'TOTAL',
                'jumlah' => $total->laki_laki + $total->perempuan,
                'laki_laki' => $total->laki_laki,
                'perempuan' => $total->perempuan,
            ],
        ];
    }
}
