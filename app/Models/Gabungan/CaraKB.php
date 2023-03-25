<?php

namespace App\Models\Gabungan;

class CaraKB extends BaseModelDBGabungan
{
    protected $table = 'tweb_cara_kb';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'cara_kb_id');
    }
}
