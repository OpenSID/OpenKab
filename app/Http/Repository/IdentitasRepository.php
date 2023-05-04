<?php

namespace App\Http\Repository;

use App\Models\Identitas;
use Spatie\QueryBuilder\QueryBuilder;

class IdentitasRepository
{
    public function identitas()
    {
        return QueryBuilder::for(Identitas::class)->first();
    }
}
