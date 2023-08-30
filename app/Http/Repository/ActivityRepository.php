<?php

namespace App\Http\Repository;

use App\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityRepository
{
    public function listActivity()
    {
        return QueryBuilder::for(Activity::with(['user']))
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::callback('created_at', function($query, $value) {
                    return $query->whereBetween('created_at', $value);
                }),
                AllowedFilter::exact('causer_id'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('properties', 'LIKE', '%'.$value.'%');
                }),
            ])->allowedSorts([
                'created_at'
            ])
            ->jsonPaginate();
    }
}
