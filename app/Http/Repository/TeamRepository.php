<?php

namespace App\Http\Repository;

use App\Models\Team;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class TeamRepository
{
    public function listTeam()
    {
        return QueryBuilder::for(Team::class)
        ->allowedFilters([
            AllowedFilter::exact('name'),

        ])
        ->allowedSorts(['name']);
    }

}
