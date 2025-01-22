<?php

namespace App\Http\Transformers;

use App\Models\Config;
use League\Fractal\TransformerAbstract;

class DesaTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Config $config)
    {
        $config->load('sebutanDesa');

        return [
            'id' => $config->id,
            'nama_desa' => $config->nama_desa,
            'kode_desa' => $config->kode_desa,
            'kode_pos' => $config->kode_pos,
            'nama_kecamatan' => $config->nama_kecamatan,
            'kode_kecamatan' => $config->kode_kecamatan,
            'website' => $config->website,
            'path' => $config->path,
            'sebutan_desa' => $config->sebutanDesa->value ?? '',
        ];
    }
}
