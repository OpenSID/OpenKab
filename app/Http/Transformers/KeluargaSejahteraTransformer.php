<?php

namespace App\Http\Transformers;

use App\Models\KeluargaSejahtera;
use League\Fractal\TransformerAbstract;

class KeluargaSejahteraTransformer extends TransformerAbstract
{
    public function transform(KeluargaSejahtera $keluargaSejahtera)
    {
        return [
            'id'                   => $keluargaSejahtera->id,
            'nama'                 => $keluargaSejahtera->nama,
            'jumlah'               => $keluargaSejahtera->statistik[0]['jumlah'],
            'persentase_jumlah'    => $keluargaSejahtera->statistik[0]['persentase_jumlah'],
            'laki_laki'            => $keluargaSejahtera->statistik[0]['laki_laki'],
            'persentase_laki_laki' => $keluargaSejahtera->statistik[0]['persentase_laki_laki'],
            'perempuan'            => $keluargaSejahtera->statistik[0]['perempuan'],
            'persentase_perempuan' => $keluargaSejahtera->statistik[0]['persentase_perempuan'],
            'at'         => $keluargaSejahtera->at,
            'statistik'         => $keluargaSejahtera->statistik,
        ];
    }
}
