<?php

namespace App\Models\Traits;

use Illuminate\Support\Collection;

trait EnumToArrayTrait
{
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function array(): array
    {
        return array_combine(self::values(), self::names());
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
