<?php

namespace App\Models\Gabungan;

class StatusTetap extends BaseModelDBGabungan
{
    protected $table = 'tweb_penduduk_status';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'status');
    }
}
