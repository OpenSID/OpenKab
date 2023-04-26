<?php

namespace App\Http\Transformers;

use League\Fractal\TransformerAbstract;

class StatistikTransformer extends TransformerAbstract
{
    public function transform($statistik)
    {
        return [
            'id' => $statistik['id'],
            'nama' => strtoupper($statistik['nama']),
            'jumlah' => (int) $statistik['jumlah'],
            'persentase_jumlah' => $statistik['persentase_jumlah'],
            'laki_laki' => (int) $statistik['laki_laki'],
            'persentase_laki_laki' => $statistik['persentase_laki_laki'],
            'perempuan' => (int) $statistik['perempuan'],
            'persentase_perempuan' => $statistik['persentase_perempuan'],
        ];
    }
}
