<?php

namespace App\Http\Transformers;

use App\Models\Rtm;
use League\Fractal\TransformerAbstract;

class GrafikRtmTransformer extends TransformerAbstract
{
    public function transform(Rtm $rtm)
    {
        return [
            'id' => $rtm->id,
            'nama' => $rtm->nama,
            'sasaran' => $rtm->sasaran,
            'ndesc' => $rtm->ndesc,
            'sdate' => $rtm->sdate,
            'edate' => $rtm->edate,
            'status' => $rtm->status,
            'asaldana' => $rtm->asaldana,
        ];
    }
}
