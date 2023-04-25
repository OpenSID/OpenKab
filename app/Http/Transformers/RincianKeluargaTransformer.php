<?php

namespace App\Http\Transformers;

use App\Models\Config;
use App\Models\Keluarga;
use League\Fractal\TransformerAbstract;

class RincianKeluargaTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Keluarga $keluarga)
    {
        $identitas = Config::where('id', $keluarga->config_id)->first();

        return [
            'no_kk' => $keluarga->no_kk,
            'id' => $keluarga->id,
            'alamat_plus_dusun' => ($keluarga->wilayah->dusun != '' && $keluarga->wilayah->dusun != '-') ? trim(ucwords(setting($keluarga->config_id, 'sebutan_dusun').' '.$keluarga->wilayah->dusun)) : $keluarga->wilayah->dusun,
            'rt' => $keluarga->rt ?? '-',
            'rw' => $keluarga->rw ?? '-',
            'kecamatan' => $identitas->nama_kecamatan,
            'kabupaten' => $identitas->nama_kabupaten,
            'provinsi' => $identitas->nama_propinsi,
            'kode_pos' => $identitas->kode_pos,
            'anggota' => $keluarga->anggota,
            'desa' => ucwords(setting($keluarga->config_id, 'sebutan_desa').' '.$identitas->nama_desa),
        ];
    }
}
