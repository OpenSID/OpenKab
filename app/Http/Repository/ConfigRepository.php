<?php

namespace App\Http\Repository;

use App\Models\Config;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class ConfigRepository
{
    public function desa()
    {
        return QueryBuilder::for(Config::class)->get();
    }

    public function kabupaten()
    {
        return QueryBuilder::for(Config::class)
            ->select('kode_kabupaten', DB::raw('MAX(nama_kabupaten) as nama_kabupaten'), DB::raw('MIN(id) as id'))
            ->groupBy('kode_kabupaten')
            ->cursor();
    }

    public function kecamatan()
    {
        return QueryBuilder::for(Config::class)
            ->select('kode_kecamatan', DB::raw('MAX(nama_kecamatan) as nama_kecamatan'), DB::raw('MIN(id) as id'))
            ->groupBy('kode_kecamatan')
            ->cursor();
    }
}
