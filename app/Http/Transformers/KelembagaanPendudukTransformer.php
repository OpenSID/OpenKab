<?php

namespace App\Http\Transformers;

use League\Fractal\TransformerAbstract;

class KelembagaanPendudukTransformer extends TransformerAbstract
{
    public function transform($penduduk)
    {
        return [
            'id' => $penduduk->id,
            'nik' => $penduduk->nik,
            'agama' => $penduduk->agama->nama ?? 'TIDAK TAHU',
            'suku' => $penduduk->suku ?: 'TIDAK TAHU',

        ];
    }
}
