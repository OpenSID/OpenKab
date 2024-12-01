<?php

namespace App\Http\Transformers;

use App\Models\Penduduk;
use League\Fractal\TransformerAbstract;

class KesehatanTransformer extends TransformerAbstract
{
    public function transform(Penduduk $penduduk)
    {
        return [
            'id' => $penduduk->id,
            'nama' => $penduduk->nama,
            'nik' => $penduduk->nik,
            'golongan_darah' => $penduduk->golonganDarah ? $penduduk->golonganDarah->nama : 'TIDAK TAHU',
            'cacat' => $penduduk->cacat && $penduduk->cacat->nama ? $penduduk->cacat->nama : 'TIDAK TAHU',
            'sakit_menahun' => $penduduk->sakitMenahun && $penduduk->sakitMenahun->nama ? $penduduk->sakitMenahun->nama : 'TIDAK TAHU',
            'kb' => $penduduk->kb && $penduduk->kb->nama ? $penduduk->kb->nama : 'TIDAK TAHU',
            'hamil' => $penduduk->statusHamil ?? 'TIDAK TAHU',
            'asuransi' => $penduduk->asuransi ? $penduduk->asuransi->nama : 'TIDAK TAHU',
            'no_asuransi' => $penduduk->no_asuransi ? $penduduk->no_asuransi : 'TIDAK TAHU',
            'status_gizi' => $penduduk->namaStuntingStatus ?? 'TIDAK TAHU',
        ];
    }
}
