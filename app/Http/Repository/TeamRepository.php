<?php

namespace App\Http\Repository;

use App\Models\Team;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TeamRepository
{
    public function listTeam()
    {
        return QueryBuilder::for(Team::class)
        ->allowedFilters([
            AllowedFilter::partial('name'),

        ])
        ->allowedSorts(['name']);
    }
}
