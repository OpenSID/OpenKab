<?php

namespace App\Http\Transformers;

use App\Models\Bantuan;
use App\Models\Penduduk;
use App\Models\BantuanPeserta;
use Illuminate\Support\Facades\DB;
use League\Fractal\TransformerAbstract;

class BantuanTransformer extends TransformerAbstract
{
    public function transform(Bantuan $bantuan)
    {
        return $bantuan->toArray();
    }
}
