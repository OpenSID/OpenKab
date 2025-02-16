<?php

namespace App\Http\Repository;

use App\Models\Lokasi;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PlanRepository
{
    public function listPlan()
    {
        return QueryBuilder::for(Lokasi::class)
            ->whereHas('point', function ($query) {
                $query->where('sumber', 'OpenKab');
            })
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::callback('subpoint', function ($query, $value) {
                    $query->where('ref_point', $value);
                }),
                AllowedFilter::callback('point', function ($query, $value) {
                    $query->whereIn('ref_point', function ($subQuery) use ($value) {
                        $subQuery->select('id')->from('point')->where('parrent', $value);
                    });
                }),
                AllowedFilter::callback('status', function ($query, $value) {
                    $query->where('enabled', $value);
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('nama', 'LIKE', '%'.$value.'%');
                }),
            ])
            ->allowedSorts([
                'nama',
                'enabled',
            ])
            ->with('point')
            ->jsonPaginate();
    }
}
