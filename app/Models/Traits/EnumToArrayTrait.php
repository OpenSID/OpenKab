<?php

namespace App\Models\Traits;

trait EnumToArrayTrait
{
    public static function object(): object
    {
        return collect(self::getAll())->map(function ($value, $key) {
            return [
                'id' => $key,
                'nama' => $value,
            ];
        })->values();
    }
}
