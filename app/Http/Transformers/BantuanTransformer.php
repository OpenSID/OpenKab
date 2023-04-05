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
        // hapus data statistik pada model Bantuan
        return [
            'id' => $bantuan->id,
            'nama' => $bantuan->nama,
            'sasaran' => $bantuan->sasaran,
            'ndesc' => $bantuan->ndesc,
            'sdate' => $bantuan->sdate,
            'edate' => $bantuan->edate,
            'status' => $bantuan->status,
            'asaldana' => $bantuan->asaldana,
        ];
    }
}
