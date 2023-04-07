<?php

namespace App\Http\Transformers;

use App\Models\KeluargaSejahtera;
use League\Fractal\TransformerAbstract;

class GrafikKeluargaTransformer extends TransformerAbstract
{
    public function transform(KeluargaSejahtera $keluargaSejahtera)
    {
        return [
            'id' => $keluargaSejahtera->id,
            'nama' => $keluargaSejahtera->nama,
            'statistik' => $keluargaSejahtera->statistik,
        ];
    }
}
