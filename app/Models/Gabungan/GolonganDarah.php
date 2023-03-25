<?php

namespace App\Models\Gabungan;

class GolonganDarah extends BaseModelDBGabungan
{
    protected $table = 'tweb_golongan_darah';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'golongan_darah_id');
    }
}
