<?php

namespace App\Http\Transformers;

use App\Models\Config;
use League\Fractal\TransformerAbstract;

class DesaAktifTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Config $config)
    {
        return [
            'id' => $config->id,
            'nama_desa' => $config->nama_desa,
            'nama_kecamatan' => $config->nama_kecamatan,
            'alamat' => $config->alamat_kantor,
            'website' => $config->website,
            'penduduk' => angka_lokal($config->penduduk_count),
            'keluarga' => angka_lokal($config->keluarga_count),
            'rtm' => angka_lokal($config->rtm_count),
        ];
    }
}
