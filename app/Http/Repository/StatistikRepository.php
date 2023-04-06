<?php

namespace App\Http\Repository;

class StatistikRepository
{
    public function getStatistik(array $peserta = [], array $total = [])
    {
        return [
            [
                'id'        => 1,
                'nama'      => 'Peserta',
                'jumlah'    => $peserta['jumlah'],
                'laki_laki' => $peserta['laki_laki'],
                'perempuan' => $peserta['perempuan'],
                'persentase_jumlah' => persen($peserta['jumlah'], $total['jumlah']),
                'persentase_laki_laki' => persen($peserta['laki_laki'], $total['laki_laki']),
                'persentase_perempuan' => persen($peserta['perempuan'], $total['perempuan']),
            ],
            [
                'id'        => 2,
                'nama'      => 'Bukan Peserta',
                'jumlah'    => $total['jumlah'] - $peserta['jumlah'],
                'laki_laki' => $total['laki_laki'] - $peserta['laki_laki'],
                'perempuan' => $total['perempuan'] - $peserta['perempuan'],
                'persentase_jumlah' => persen(($total['jumlah'] - $peserta['jumlah']), $total['jumlah']),
                'persentase_laki_laki' => persen(($total['laki_laki'] - $peserta['laki_laki']), $total['laki_laki']),
                'persentase_perempuan' => persen(($total['perempuan'] - $peserta['perempuan']), $total['perempuan']),
            ],
            [
                'id'        => 3,
                'nama'      => 'Total',
                'jumlah'    => $total['jumlah'],
                'laki_laki' => $total['laki_laki'],
                'perempuan' => $total['perempuan'],
                'persentase_jumlah' => persen(100, 100),
                'persentase_laki_laki' => persen(100, 100),
                'persentase_perempuan' => persen(100, 100),
            ],
        ];
    }
}
