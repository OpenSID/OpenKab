<?php

namespace App\Http\Repository;

use App\Models\Config;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DesaRepository
{
    public function list()
    {
        return  QueryBuilder::for(Config::with('sebutanDesa'))
            ->allowedFilters([
                AllowedFilter::exact('kode_kecamatan'),              
            ])            
            ->allowedFields('*')
            ->jsonPaginate();
    }    
}
