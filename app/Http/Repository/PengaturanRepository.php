<?php

namespace App\Http\Repository;

use App\Models\Config;
use App\Models\Pengaturan;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PengaturanRepository
{
    public function listPengaturan()
    {
        return QueryBuilder::for(Pengaturan::class)
            ->where('config_id', Config::first()->id)
            ->allowedFilters([
                AllowedFilter::callback('key', function ($query, $value) {
                    $query->whereIn('key', $value);
                }),
            ])
            ->where('kategori', 'openkab')
            ->get()
            ->unique('key');
    }
}
