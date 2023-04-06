<?php

namespace App\Http\Repository;

class StatistikRepository
{
    public function getStatistik(array $peserta = [], array $total = [])
    {
        return [
            [
                'id'        => 1,
                'jumlah'    => $peserta['jumlah'],
                'laki_laki' => $peserta['laki_laki'],
                'perempuan' => $peserta['perempuan'],
                'persentase_jumlah' => $total['jumlah'] > 0 ? $peserta['jumlah'] / $total['jumlah'] * 100 : 0,
                'persentase_laki_laki' => $total['laki_laki'] > 0 ? $peserta['laki_laki'] / $total['laki_laki'] * 100 : 0,
                'persentase_perempuan' => $total['perempuan'] > 0 ? $peserta['perempuan'] / $total['perempuan'] * 100 : 0,
                'nama'      => 'Peserta',
            ],
            [
                'id'        => 2,
                'jumlah'    => $total['jumlah'] - $peserta['jumlah'],
                'laki_laki' => $total['laki_laki'] - $peserta['laki_laki'],
                'perempuan' => $total['perempuan'] - $peserta['perempuan'],
                'persentase_jumlah' => $total['jumlah'] > 0 ? ($total['jumlah'] - $peserta['jumlah']) / $total['jumlah'] * 100 : 0,
                'persentase_laki_laki' => $total['laki_laki'] > 0 ? ($total['laki_laki'] - $peserta['laki_laki']) / $total['laki_laki'] * 100 : 0,
                'persentase_perempuan' => $total['perempuan'] > 0 ? ($total['perempuan'] - $peserta['perempuan']) / $total['perempuan'] * 100 : 0,
                'nama'      => 'Bukan Peserta',
            ],
            [
                'id'        => 3,
                'jumlah'    => $total['jumlah'],
                'laki_laki' => $total['laki_laki'],
                'perempuan' => $total['perempuan'],
                'persentase_jumlah' => 100,
                'persentase_laki_laki' => 100,
                'persentase_perempuan' => 100,
                'nama'      => 'Total',
            ],
        ];
    }
}
