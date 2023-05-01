<?php

namespace App\Http\Transformers;

use App\Models\Config;
use League\Fractal\TransformerAbstract;

class ArtikelTransformer extends TransformerAbstract
{
    public function transform(Config $config)
    {
        return [
            'id' => $config->id,
            'nama_desa' => $config->nama_desa,
            'jumlah_artikel' => $config->jumlah,
        ];
    }
}
