<?php

namespace App\Http\Repository;

use App\Models\Rtm;
use App\Models\Bantuan;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class RtmRepository
{
    public function listRtm()
    {
        $query = Rtm::query();

        if (session()->has('desa')) {
            $query->where('config_id', session('desa.id'));
        }

        return QueryBuilder::for($query)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'nama',
                'sasaran',
            ])
            ->allowedSorts([
                'nama',
                'sasaran',
            ])
            ->jsonPaginate();
    }
}
