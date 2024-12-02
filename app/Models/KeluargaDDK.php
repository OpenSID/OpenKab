<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class KeluargaDDK extends Keluarga
{
    public function scopeWithoutDefaultRelations($query)
    {
        return $query->without($this->newInstance()->with);
    }

    /**
     * Define a one-to-one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function kepalaKeluarga()
    {
        return $this->hasOne(Penduduk::class, 'id', 'nik_kepala');
    }

    /**
     * Define a one-to-many relationship.
     *
     * @return HasMany
     */
    public function anggota()
    {
        return $this->hasMany(Penduduk::class, 'id_kk')
            ->status(1)
            ->orderBy('kk_level')
            ->orderBy('tanggallahir');
    }

    /**
     * Define a one-to-one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function prodeskelDDK()
    {
        return $this->hasOne(ProdeskelDDK::class, 'keluarga_id');
    }
}
