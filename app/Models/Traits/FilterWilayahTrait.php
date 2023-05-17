<?php

namespace App\Models\Traits;

trait FilterWilayahTrait
{
    /**
     * Scope query untuk filter Kecamatan.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeFilterKecamatan($query)
    {
        if (request('kode_kecamatan')) {
            return $query->whereIn('config_id', function ($kecamatan) {
                return $kecamatan->selectRaw('c.id from config as c where c.kode_kecamatan = '.request('kode_kecamatan'));
            });
        }

        return $query;
    }

    /**
     * Scope query untuk filter Desa.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeFilterDesa($query)
    {
        if (request('config_desa')) {
            return $query->where('config_id', request('config_desa'));
        }

        return $query;
    }

    /**
     * Scope query untuk filter Wilayah berdasarkan Kecamatan dan Desa.
     *
     * @param Builder $query
     * @param mixed   $value
     *
     * @return Builder
     */
    public function scopeFilterWilayah($query)
    {
        return $query->filterDesa($this->scopeFilterKecamatan($query));
    }
}
