<?php

namespace App\Http\Repository;

use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ActivityRepository
{
    public function listActivity()
    {
        return QueryBuilder::for(Activity::class)
            ->allowedFields('*')
            // ->allowedFilters([
            //     // AllowedFilter::exact('id'),
            //     // AllowedFilter::exact('id_pend'),
            // ])
            ->jsonPaginate();
    }
}
