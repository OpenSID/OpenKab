<?php

namespace App\Models\Gabungan;

class StatusHamil extends BaseModelDBGabungan
{
    protected $table = 'ref_penduduk_hamil';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'hamil')
            ->perempuan();
    }
}
