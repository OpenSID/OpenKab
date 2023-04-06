<?php

namespace App\Http\Transformers;

use App\Models\Rtm;
use Illuminate\Support\Facades\DB;
use League\Fractal\TransformerAbstract;

class StatistikTransformer extends TransformerAbstract
{
    public function transform($statistik)
    {
        return [
            'id' => $statistik['id'],
            'nama' => $statistik['nama'],
            'jumlah' => $statistik['jumlah'],
            'persentase_jumlah' => $statistik['persentase_jumlah'],
            'laki_laki' => $statistik['laki_laki'],
            'persentase_laki_laki' => $statistik['persentase_laki_laki'],
            'perempuan' => $statistik['perempuan'],
            'persentase_perempuan' => $statistik['persentase_perempuan'],
        ];
    }
}
