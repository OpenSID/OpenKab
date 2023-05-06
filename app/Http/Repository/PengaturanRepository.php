<?php

namespace App\Http\Repository;

use App\Models\Pengaturan;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PengaturanRepository
{
    public function listPengaturan()
    {
        return QueryBuilder::for(Pengaturan::class)
            ->allowedFilters([
                AllowedFilter::callback('key', function ($query, $value) {
                    $query->whereIn('key', $value);
                }),
            ])->get();
    }
}
