<?php

namespace App\Models\Traits;

use App\Models\Scopes\ConfigIdScope;

/**
 * @method static static|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder withConfigId(bool $alias = null)
 */
trait ConfigIdTrait
{
    public static function bootConfigID()
    {
        static::addGlobalScope(new ConfigIdScope());
    }
}