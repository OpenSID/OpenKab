<?php

namespace App\Http\Transformers;

use App\Models\Pengaturan;
use League\Fractal\TransformerAbstract;

class PengaturanTransformer extends TransformerAbstract
{
    public function transform(Pengaturan $pengaturan)
    {
        return [
            'id' => $pengaturan->id,
            'config_id' => $pengaturan->config_id,
            'judul' => $pengaturan->judul,
            'key' => $pengaturan->key,
            'value' => $pengaturan->value,
            'keterangan' => $pengaturan->keterangan,
            'jenis' => $pengaturan->jenis,
            'option' => $pengaturan->option,
            'attribute' => $pengaturan->attribute,
            'kategori' => $pengaturan->kategori,
        ];
    }
}
