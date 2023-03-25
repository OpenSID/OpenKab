<?php

namespace App\Models\Gabungan;

class PendidikanKK extends BaseModelDBGabungan
{
    protected $table = 'tweb_penduduk_pendidikan_kk';

    public function penduduk()
    {
        return $this->hasMany(Penduduk::class, 'pendidikan_kk_id');
    }
}
