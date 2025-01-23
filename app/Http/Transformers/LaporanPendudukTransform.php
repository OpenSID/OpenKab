<?php

namespace App\Http\Transformers;

use App\Models\LaporanSinkronisasi;
use League\Fractal\TransformerAbstract;

class LaporanPendudukTransform extends TransformerAbstract
{
    public function transform(LaporanSinkronisasi $penduduk)
    {
        return [
            'id' => $penduduk->id,
            'config' => $penduduk->config_id,
            'tipe' => $penduduk->tipe,
            'judul' => $penduduk->judul,
            'tahun' => $penduduk->tahun,
            'bulan' => $penduduk->semester,
            'nama_file' => $penduduk->nama_file,
            'kirim' => $penduduk->kirim,
        ];
    }
}
