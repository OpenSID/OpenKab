<?php

namespace App\Http\Transformers;

use App\Models\Penduduk;
use League\Fractal\TransformerAbstract;

class DtksTransformer extends TransformerAbstract
{
    public function transform(Penduduk $penduduk)
    {
        return [
            'id' => $penduduk->id,
            'nama' => $penduduk->nama,
            'nik' => $penduduk->nik,
            'dtks' => $penduduk->dtksAnggota ? 'Terdaftar' : 'Tidak Terdaftar',
            'asuransi' => $penduduk?->asuransi?->nama ?? 'TIDAK TAHU',
            'no_asuransi' => $penduduk->no_asuransi ? $penduduk->no_asuransi : 'TIDAK TAHU',
            'kd_ikut_prakerja' => $penduduk->dtksAnggota ? $penduduk->dtksAnggota->kd_ikut_prakerja : 'TIDAK TAHU',
            'kd_kur' => $penduduk->dtksAnggota ? $penduduk->dtksAnggota->kd_ikut_kur : 'TIDAK TAHU',
            'kd_umi' => $penduduk->dtksAnggota ? $penduduk->dtksAnggota->kd_ikut_umi : 'TIDAK TAHU',
            'bpjs_ketenagakerjaan' => $penduduk->bpjs_ketenagakerjaan ?? 'TIDAK TAHU',
            'cacat' => $penduduk->cacat && $penduduk->cacat->nama ? $penduduk->cacat->nama : 'TIDAK TAHU',
        ];
    }
}
