<?php

namespace App\Http\Transformers;

use App\Models\LaporanSinkronisasi;
use League\Fractal\TransformerAbstract;

class LaporanSinkronisasiTransformer extends TransformerAbstract
{
    private $lokasiDokumen = 'desa/upload/dokumen/';

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(LaporanSinkronisasi $sinkronisasi)
    {
        $sinkronisasi->created_at_local = $sinkronisasi->created_at?->format('d-m-Y H:i:s');
        $sinkronisasi->url_file = ($sinkronisasi->website ?? 'http://desatidakketemu.id').'/'.$this->lokasiDokumen.$sinkronisasi->nama_file;

        return $sinkronisasi->toArray();
    }
}
