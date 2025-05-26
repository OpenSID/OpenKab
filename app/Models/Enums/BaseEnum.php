<?php

namespace App\Models\Enums;

use BenSampo\Enum\Enum;

class BaseEnum extends Enum
{
    public static function select2(): array
    {
        return array_merge([['id' => '', 'text' => 'Pilih']], collect(static::all())
            ->map(function ($value, $key) {
                return [
                    'id' => $key,
                    'text' => $value,
                ];
            })
            ->values()
            ->toArray());
    }
}
