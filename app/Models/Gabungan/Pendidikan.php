<?php

namespace App\Models\Gabungan;

class Pendidikan extends BaseModelDBGabungan
{
    protected $table = 'tweb_penduduk_pendidikan';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'pendidikan_sedang_id');
    }
}
