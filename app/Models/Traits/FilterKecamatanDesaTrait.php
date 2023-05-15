<?php

namespace App\Models\Traits;

trait FilterKecamatanDesaTrait
{
    /**
     * Scope query untuk filter Kecamatan.
     *
     * @param Builder $query
     * @param mixed   $value
     *
     * @return Builder
     */
    public function scopeFilterKecamatan($query)
    {
        if (request()->get('kode_kecamatan')) {
            return $query->whereIn('config_id', function ($kecamatan) {
                return $kecamatan->selectRaw('c.id from config as c where c.kode_kecamatan = '.request()->get('kode_kecamatan'));
            });
        }
    }

    /**
     * Scope query untuk filter Desa berdasarkan.
     *
     * @param Builder $query
     * @param mixed   $value
     *
     * @return Builder
     */
    public function scopeFilterDesa($query)
    {
        if (request()->get('config_desa')) {
            return $query->where('config_id', request()->get('config_desa'));
        }
    }
}
