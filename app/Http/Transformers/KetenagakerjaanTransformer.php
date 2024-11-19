<?php

namespace App\Http\Transformers;

use App\Models\Penduduk;
use League\Fractal\TransformerAbstract;

class KetenagakerjaanTransformer extends TransformerAbstract
{
    public function transform(Penduduk $penduduk)
    {
        return [
            'id' => $penduduk->id,
            'nik' => $penduduk->nik,
            'pekerjaan' => $penduduk->pekerjaan?->nama,
            'jabatan' => $penduduk->config?->hoKeluarga?->ProdeskelDDK?->jabatan ?? 'TIDAK TAHU',
            'jumlah_penghasilan' => $penduduk->config?->hoKeluarga?->ProdeskelDDK?->jumlah_penghasilan_perbulan ?? 'TIDAK TAHU',
            'pelatihan' => $penduduk->kaderPemberdayaanMasyarakat?->pendudukKursus?->nama ?? 'TIDAK TAHU',
        ];
    }
}
