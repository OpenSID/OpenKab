<?php

namespace App\Http\Transformers;

use App\Models\Keuangan;
use League\Fractal\TransformerAbstract;

class KeuanganTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Keuangan $keuangan)
    {
        $keuangan->anggaran_local = 'Rp. '.angka_lokal($keuangan->anggaran);
        $keuangan->realisasi_local = 'Rp. '.angka_lokal($keuangan->realisasi);

        return $keuangan->toArray();
    }
}
