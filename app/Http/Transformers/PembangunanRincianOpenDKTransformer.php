<?php

namespace App\Http\Transformers;

use App\Models\PembangunanRincian;
use League\Fractal\TransformerAbstract;

class PembangunanRincianOpenDKTransformer extends TransformerAbstract
{
    public function transform(PembangunanRincian $pembangunanRincian)
    {
        return [
            'id' => $pembangunanRincian->id,
            'persentase' => $pembangunanRincian->persentase,
            'keterangan' => $pembangunanRincian->keterangan,
            'created_at' => $pembangunanRincian->created_at,
            'config' => $pembangunanRincian->config,
        ];
    }
}
