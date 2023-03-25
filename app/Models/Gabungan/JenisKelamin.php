<?php

namespace App\Models\Gabungan;

use App\Models\Gabungan\BaseModelDBGabungan;

class JenisKelamin extends BaseModelDBGabungan
{
    protected $table = 'tweb_penduduk_sex';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'sex');
    }
}
