<?php

namespace App\Models\Gabungan;

class HubunganKepalaKeluarga extends BaseModelDBGabungan
{
    protected $table = 'tweb_penduduk_hubungan';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'kk_level');
    }
}
