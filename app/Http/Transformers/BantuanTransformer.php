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
        return [
            'id' => $bantuan->id,
            'nama' => $bantuan->nama,
            'sasaran' => $bantuan->sasaran,
            'nama_sasaran' => $bantuan->nama_sasaran,
            'jumlah_peserta' => $bantuan->jumlah_peserta,
            'ndesc' => $bantuan->ndesc,
            'sdate' => $bantuan->sdate,
            'edate' => $bantuan->edate,
            'status' => $bantuan->status,
            'asaldana' => $bantuan->asaldana,
        ];
    }
}
