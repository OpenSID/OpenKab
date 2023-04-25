<?php

namespace App\Http\Transformers;

use App\Models\Penduduk;
use League\Fractal\TransformerAbstract;

class PendudukTransformer extends TransformerAbstract
{
    public function transform(Penduduk $penduduk)
    {
        return $penduduk->toArray();
    }
}
