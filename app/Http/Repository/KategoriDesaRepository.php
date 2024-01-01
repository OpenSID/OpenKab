<?php

namespace App\Http\Repository;

use App\Models\Config;
use Spatie\QueryBuilder\QueryBuilder;

class KategoriDesaRepository
{
    public function aktif()
    {
        return  QueryBuilder::for(Config::whereHas('penduduk')
                ->withCount('penduduk')
                ->withCount('rtm')
                ->withCount('keluarga')
                ->orderByArtikel()
                ->orderByTraffic()
        )
            ->allowedFields('*')
            ->jsonPaginate();
    }
}
