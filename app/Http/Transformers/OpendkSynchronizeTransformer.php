<?php

namespace App\Http\Transformers;

use App\Models\OpendkSynchronize;
use League\Fractal\TransformerAbstract;

class OpendkSynchronizeTransformer extends TransformerAbstract
{
    public function transform(OpendkSynchronize $sync)
    {
        return [
            'id' => $sync->id,
            'nama_kecamatan' => $sync->nama_kecamatan,
            'kode_kecamatan' => $sync->kode_kecamatan,
            'updated_at' => $sync->updated_at->format('d M Y, H:i:s'),
        ];
    }
}
