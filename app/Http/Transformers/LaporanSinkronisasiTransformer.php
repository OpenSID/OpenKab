<?php

namespace App\Http\Transformers;

use App\Models\LaporanSinkronisasi;
use League\Fractal\TransformerAbstract;

class LaporanSinkronisasiTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(LaporanSinkronisasi $sinkronisasi)
    {
        $sinkronisasi->created_at_local = $sinkronisasi->created_at?->format('d F Y');

        return $sinkronisasi->toArray();
    }
}
