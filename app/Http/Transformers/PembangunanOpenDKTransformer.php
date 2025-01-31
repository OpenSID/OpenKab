<?php

namespace App\Http\Transformers;

use App\Models\Pembangunan;
use League\Fractal\TransformerAbstract;

class PembangunanOPenDKTransformer extends TransformerAbstract
{
    public function transform(Pembangunan $pembangunan)
    {
        return [
            'id' => $pembangunan->id,
            'judul' => $pembangunan->judul,
            'sumber_dana' => $pembangunan->sumber_dana,
            'anggaran' => $pembangunan->anggaran,
            'volume' => $pembangunan->volume,
            'tahun_anggaran' => $pembangunan->tahun_anggaran,
            'pelaksana_kegiatan' => $pembangunan->pelaksana_kegiatan,
            'lokasi' => $pembangunan->lokasi,
            'keterangan' => $pembangunan->keterangan,
            'config' => $pembangunan->config,
        ];
    }
}
