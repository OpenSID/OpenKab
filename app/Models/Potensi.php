<?php

namespace App\Models;

use App\Models\Traits\FilterWilayahTrait;
use Illuminate\Database\Eloquent\Builder;

class Potensi extends BaseModel
{
    use FilterWilayahTrait;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prodeskel_potensi';

    /**
     * The casts with the model.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'json',
    ];

    protected function scopeBatasWilayah(Builder $query)
    {
        return $query->where('kategori', 'batas-wilayah');
    }

    protected function scopeJenisLahan(Builder $query)
    {
        return $query->where('kategori', 'jenis-lahan');
    }

    protected function scopeIklim(Builder $query)
    {
        return $query->where('kategori', 'iklim');
    }

    protected function scopeTopografi(Builder $query)
    {
        return $query->where('kategori', 'topografi');
    }

    protected function scopeKepemilikanLahan(Builder $query)
    {
        return $query->where('kategori', 'kepemilikan-lahan');
    }

    protected function scopeKepemilikanLahanBuah(Builder $query)
    {
        return $query->where('kategori', 'kepemilikan-lahan-buah');
    }

    protected function scopeKepemilikanLahanKebun(Builder $query)
    {
        return $query->where('kategori', 'kepemilikan-lahan-kebun');
    }

    protected function scopeLahanPakanTernak(Builder $query)
    {
        return $query->where('kategori', 'lahan-dan-pakan-ternak');
    }

    protected function scopeKepemilikanLahanHutan(Builder $query)
    {
        return $query->where('kategori', 'kepemilikan-lahan-hutan');
    }

    protected function scopeDampakPengolahanHutan(Builder $query)
    {
        return $query->where('kategori', 'dampak-pengolahan-hutan');
    }

    protected function scopeKondisiHutan(Builder $query)
    {
        return $query->where('kategori', 'kondisi-hutan');
    }

    protected function scopeSdmUsia(Builder $query)
    {
        return $query->where('kategori', 'sdm-usia');
    }

    protected function scopeSdmJumlah(Builder $query)
    {
        return $query->where('kategori', 'sdm-jumlah');
    }

    protected function scopeLembagaPemerintah(Builder $query)
    {
        return $query->where('kategori', 'lembaga-pemerintah');
    }

    protected function scopePrasaranaLembagaKemasyarakatan(Builder $query)
    {
        return $query->where('kategori', 'prasarana-lembaga-kemasyarakatan');
    }

    protected function scopePrasaranaIrigasi(Builder $query)
    {
        return $query->where('kategori', 'prasarana-irigasi');
    }

    protected function scopeSanitasi(Builder $query)
    {
        return $query->where('kategori', 'sanitasi');
    }

    protected function scopeSaranaEnergi(Builder $query)
    {
        return $query->where('kategori', 'sarana-energi');
    }

    protected function scopeAirBersih(Builder $query)
    {
        return $query->where('kategori', 'air-bersih');
    }

    protected function scopeLembagaAdat(Builder $query)
    {
        return $query->where('kategori', 'lembaga-adat');
    }

    protected function scopeSaranaKebersihan(Builder $query)
    {
        return $query->where('kategori', 'sarana-kebersihan');
    }

    protected function scopeSaranaDesa(Builder $query)
    {
        return $query->where('kategori', 'sarana-desa');
    }

    protected function scopeSaranaDusun(Builder $query)
    {
        return $query->where('kategori', 'sarana-dusun');
    }

    protected function scopeBadanPerwakilanDesa(Builder $query)
    {
        return $query->where('kategori', 'badan-perwakilan-desa');
    }

    protected function scopePrasaranaPeribadatan(Builder $query)
    {
        return $query->where('kategori', 'prasarana-peribadatan');
    }
}
