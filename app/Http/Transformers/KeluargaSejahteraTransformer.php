<?php

namespace App\Http\Transformers;

use App\Models\KeluargaSejahtera;
use League\Fractal\TransformerAbstract;

class KeluargaSejahteraTransformer extends TransformerAbstract
{
    public function transform(KeluargaSejahtera $keluargaSejahtera)
    {
        return $keluargaSejahtera->toArray();
    }
}
