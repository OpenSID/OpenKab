<?php

namespace App\Models\Gabungan;

class WargaNegara extends BaseModelDBGabungan
{
    protected $table = 'tweb_penduduk_warganegara';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'warganegara_id');
    }
}
