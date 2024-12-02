<?php

namespace App\Models;

class ProdeskelPotensi extends BaseModel
{
    /** {@inheritdoc} */
    protected $table = 'prodeskel_potensi';

    // Relasi ke Penduduk
    public function penduduk()
    {
        return $this->belongsTo(Penduduk::class, 'config_id', 'config_id');
    }

    // Scope untuk kategori lembaga adat
    public function scopeLembagaAdat($query)
    {
        return $query->where('kategori', 'lembaga-adat');
    }

    // Scope untuk kategori lembaga adat
    public function scopePrasaranaPeribadatan($query)
    {
        return $query->where('kategori', 'prasarana-peribadatan');
    }
}
