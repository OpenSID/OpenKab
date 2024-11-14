<?php

namespace App\Models;

class DtksAnggota extends BaseModel
{
    /** {@inheritdoc} */
    protected $table = 'dtks_anggota';
    
    public function penduduk()
    {
        return $this->belongsTo(Penduduk::class, 'id');  // Asumsi anak_id di Kia mengarah ke Penduduk
    }
}