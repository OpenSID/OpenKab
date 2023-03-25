<?php

namespace App\Models\Gabungan;

class StatusKawin extends BaseModelDBGabungan
{
    protected $table = 'tweb_penduduk_kawin';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'status_kawin');
    }
}
