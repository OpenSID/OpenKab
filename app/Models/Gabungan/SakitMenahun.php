<?php

namespace App\Models\Gabungan;

class SakitMenahun extends BaseModelDBGabungan
{
    protected $table = 'tweb_sakit_menahun';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'sakit_menahun_id');
    }
}
