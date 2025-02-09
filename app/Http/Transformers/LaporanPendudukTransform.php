<?php

namespace App\Http\Transformers;

use App\Models\LaporanSinkronisasi;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class LaporanPendudukTransform extends TransformerAbstract
{
    public function transform(LaporanSinkronisasi $penduduk)
    {
        return [
            'id' => $penduduk->id,
            'config' => $penduduk->config,
            'judul' => $penduduk->judul,
            'tahun' => $penduduk->tahun,
            'bulan' => $penduduk->semester,
            'nama_file' => $penduduk->nama_file,
            'kirim' => $penduduk->kirim,
            'tanggal_lapor' => Carbon::parse($penduduk->created_at)->format('d-m-Y H:i:s'),
        ];
    }
}
