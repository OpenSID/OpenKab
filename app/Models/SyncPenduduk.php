<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncPenduduk extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Relation Methods
     * */
    public function getPendudukAktif($did, $year)
    {
        $penduduk = $this
            ->where('status_dasar', 1)
            ->whereYear('created_at', '<=', $year);

        if ($did != 'Semua') {
            $penduduk->where('desa_id', $did);
        }

        return $penduduk;
    }

    public function scopeHidup($query)
    {
        return $query->where('status_dasar', 1);
    }

    public function pekerjaan()
    {
        return $this->hasOne(Pekerjaan::class, 'id', 'pekerjaan_id');
    }

    public function statusKawin()
    {
        return $this->hasOne(StatusKawin::class, 'id', 'status_kawin');
    }

    public function pendidikan_kk()
    {
        return $this->hasOne(PendidikanKK::class, 'id', 'pendidikan_kk_id');
    }

    public function keluarga()
    {
        return $this->hasOne(Keluarga::class, 'no_kk', 'no_kk');
    }

    public function suplemen_terdata()
    {
        return $this->hasMany(SuplemenTerdata::class, 'penduduk_id', 'id');
    }

}
