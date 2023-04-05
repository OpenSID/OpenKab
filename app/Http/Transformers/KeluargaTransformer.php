<?php

namespace App\Http\Transformers;

use App\Models\Keluarga;
use League\Fractal\TransformerAbstract;

class KeluargaTransformer extends TransformerAbstract
{
    public function transform(Keluarga $keluarga)
    {
        return $keluarga->toArray();
    }
}
