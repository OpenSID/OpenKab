<?php

namespace App\Models\Traits;

use App\Models\Scopes\ConfigIdScope;

trait ConfigIdTrait
{
    public static function bootConfigIdTrait()
    {
        static::addGlobalScope(new ConfigIdScope());
    }
}
