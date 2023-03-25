<?php

namespace App\Models\Gabungan;

class Cacat extends BaseModelDBGabungan
{
    protected $table = 'tweb_cacat';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'cacat_id');
    }
}
