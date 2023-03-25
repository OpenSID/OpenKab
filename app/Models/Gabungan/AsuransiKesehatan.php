<?php

namespace App\Models\Gabungan;

class AsuransiKesehatan extends BaseModelDBGabungan
{
    protected $table = 'tweb_penduduk_asuransi';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'id_asuransi');
    }
}
