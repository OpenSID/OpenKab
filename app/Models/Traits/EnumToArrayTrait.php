<?php

namespace App\Models\Traits;

use Illuminate\Support\Collection;

trait EnumToArrayTrait
{
    public static function array(): array
    {
        return array_combine(self::getValues(), self::getKeys());
    }

    public static function object(): object
    {
        $collection = new Collection();
        foreach (self::array() as $key => $value) {
            $collection->push(['id' => $key, 'text' => $value]);
        }

        return $collection;
    }
}
