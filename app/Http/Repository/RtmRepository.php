<?php

namespace App\Http\Repository;

use App\Models\Rtm;

class RtmRepository
{
    public function listStatistik(): array|object
    {
        return [
            'header' => [],
            'footer' => $this->listFooter(),
        ];
    }

    public function listFooter(): array|object
    {
        $rtm = Rtm::countStatistik();

        $jumlah = $rtm->bdt(true)->get();
        $jumlah_laki_laki = $jumlah->sum('laki_laki');
        $jumlah_perempuan = $jumlah->sum('perempuan');
        $jumlah = $jumlah_laki_laki + $jumlah_perempuan;

        $total = $rtm->get();
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
