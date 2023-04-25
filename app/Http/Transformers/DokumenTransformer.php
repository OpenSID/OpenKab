<?php

namespace App\Http\Transformers;

use App\Models\DokumenHidup;
use League\Fractal\TransformerAbstract;

class DokumenTransformer extends TransformerAbstract
{
    public function transform(DokumenHidup $dokumen)
    {
        return $dokumen->toArray();
    }
}
