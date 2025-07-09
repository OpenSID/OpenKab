<?php

namespace App\Models\CMS;

use Database\Factories\VisitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Shetabit\Visitor\Models\Visit as BaseVisit;

class Visit extends BaseVisit
{
    use HasFactory;

    protected static function newFactory()
    {
        return VisitFactory::new();
    }
}
