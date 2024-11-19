<?php

namespace App\Http\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Komoditas;
use App\Models\Enums\SaranaTransportasiDaratEnum;

class PrasaraSaranaTransformer extends TransformerAbstract
{
    public function transform(Komoditas $komoditas)
    {
        // hide created and updated_at
        $komoditas = $komoditas->setHidden(['created_at', 'updated_at']);

        return $komoditas->toArray();
    }
}