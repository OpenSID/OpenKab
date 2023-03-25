<?php

namespace App\Models\Gabungan;

class Agama extends BaseModelDBGabungan
{
    protected $table = 'tweb_penduduk_agama';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'agama_id');
    }
}
