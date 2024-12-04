<?php

namespace App\Http\Transformers;

use App\Models\Enums\JenisKelaminEnum;
use App\Models\SuplemenTerdata;
use League\Fractal\TransformerAbstract;

class SuplemenTerdataTransformer extends TransformerAbstract
{
    public function transform(SuplemenTerdata $suplemen)
    {
        // Transform data suplemen
        return [
            'id' => $suplemen->id,
            'terdata_info' => $suplemen->no_kk,
            'terdata_plus' => $suplemen->nik,
            'terdata_nama' => $suplemen->terdata_nama,
            'tempatlahir' => $suplemen->tempatlahir,
            'tanggallahir' => $suplemen->tanggallahir,
            'sex' => JenisKelaminEnum::getLabel($suplemen->sex),
            'alamat' => $suplemen->alamat,
            'keterangan' => $suplemen->keterangan,
        ];
    }
}
