<?php

namespace App\Models;

class Artikel extends BaseModel
{
    /** {@inheritdoc} */
    protected $table = 'artikel';
<<<<<<< HEAD
=======

    public function scopeTahun($query)
    {
        return $query->selectRaw('YEAR(MIN(tgl_upload)) AS tahun_awal, YEAR(MAX(tgl_upload)) AS tahun_akhir');
    }
>>>>>>> 9d877b828ce1bb099dda6c8a36f21dd6716748d8
}
