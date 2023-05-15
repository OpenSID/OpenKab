<?php

namespace App\Http\Transformers;

use App\Models\Identitas;
use League\Fractal\TransformerAbstract;

class IdentitasTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Identitas $identitas)
    {
        return [
            'id' => $identitas->id,
            'nama_aplikasi' => $identitas->nama_aplikasi,
            'deskripsi' => $identitas->deskripsi,
            'logo' => $identitas->logo,
            'nama_kabupaten' => $identitas->nama_kabupaten,
            'kode_kabupaten' => $identitas->kode_kabupaten,
            'nama_provinsi' => $identitas->nama_provinsi,
            'kode_provinsi' => $identitas->kode_provinsi,
            'sebutan_kab' => $identitas->sebutan_kab,
        ];
    }
}
