<?php

namespace App\Http\Transformers;

use App\Models\PembangunanRincian;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PembangunanRincianOpenDKTransformer extends TransformerAbstract
{
    public function transform(PembangunanRincian $pembangunanRincian)
    {
        return [
            'id' => $pembangunanRincian->id,
            'persentase' => $pembangunanRincian->persentase,
            'keterangan' => $pembangunanRincian->keterangan,
            'created_at' => (Carbon::parse($pembangunanRincian->created_at))->format(config('app.format.date')),
            'config' => $pembangunanRincian->config,
        ];
    }
}
