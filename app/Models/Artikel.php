<?php

namespace App\Models;

class Artikel extends BaseModel
{
    /** {@inheritdoc} */
    protected $table = 'artikel';

    public function scopeTahun($query)
    {
        return $query->selectRaw('YEAR(MIN(tgl_upload)) AS tahun_awal, YEAR(MAX(tgl_upload)) AS tahun_akhir');
    }
}
