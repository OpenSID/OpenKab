<?php

namespace App\Http\Transformers;

use App\Models\Berita;
use League\Fractal\TransformerAbstract;

class BeritaTransformer extends TransformerAbstract
{
    public function transform(Berita $berita)
    {
        return $berita->toArray();
    }
}
