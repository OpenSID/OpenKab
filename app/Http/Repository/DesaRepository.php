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
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('nama_desa', 'like', "%{$value}%");
                        $query->where('kode_desa', 'like', "%{$value}%");
                    });
                }),              
            ])            
            ->allowedFields('*')
            ->jsonPaginate();
    }    
}
