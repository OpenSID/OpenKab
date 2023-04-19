<?php

namespace App\Http\Transformers;

use App\Models\Bantuan;
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
            'nama_status' => $bantuan->nama_status,
            'asaldana' => $bantuan->asaldana,
        ];
    }
}
