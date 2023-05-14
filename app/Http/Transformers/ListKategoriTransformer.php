<?php

namespace App\Http\Transformers;

use App\Models\Kategori;
use League\Fractal\TransformerAbstract;

class ListKategoriTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Kategori $kategori)
    {
        return [
            'id' => $kategori->id,
            'tipe' => $kategori->tipe,
            'urut' => $kategori->urut,
            'slug' => $kategori->slug,
            'parrent' => $kategori->parrent,
            'kategori' => $kategori->kategori,
            'jml_artikel' => $kategori->jml_artikel,
        ];
    }
}
