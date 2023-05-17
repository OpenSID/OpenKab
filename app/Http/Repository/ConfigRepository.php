<?php

namespace App\Http\Repository;

use App\Models\Config;
use Spatie\QueryBuilder\QueryBuilder;

class ConfigRepository
{
    public function desa()
    {
        return QueryBuilder::for(Config::class)->get();
    }

    public function kecamatan()
    {
        return QueryBuilder::for(Config::class)->group_by('kode_kecamatan')->get();
    }
}
