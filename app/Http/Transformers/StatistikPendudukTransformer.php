<?php

namespace App\Http\Transformers;

use App\Models\LaporanPenduduk;
use App\Models\Penduduk;
use App\Models\BantuanPeserta;
use Illuminate\Support\Facades\DB;
use League\Fractal\TransformerAbstract;

class StatistikPendudukTransformer extends TransformerAbstract
{
    public function transform(LaporanPenduduk $bantuan)
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
