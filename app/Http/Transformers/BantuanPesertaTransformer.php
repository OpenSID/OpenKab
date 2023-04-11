<?php

namespace App\Http\Transformers;

use App\Models\Bantuan;
use App\Models\Penduduk;
use App\Models\BantuanPeserta;
use Illuminate\Support\Facades\DB;
use League\Fractal\TransformerAbstract;

class BantuanPesertaTransformer extends TransformerAbstract
{
    public function transform(BantuanPeserta $bantuanPeserta)
    {
        return $bantuanPeserta->toArray();
    }
}
