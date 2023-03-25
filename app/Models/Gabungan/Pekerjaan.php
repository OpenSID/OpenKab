<?php

namespace App\Models\Gabungan;

class Pekerjaan extends BaseModelDBGabungan
{
    protected $table = 'tweb_penduduk_pekerjaan';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'pekerjaan_id');
    }
}
