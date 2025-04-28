<?php

namespace App\Enums;

enum AccessTypeEnum
{
    case LOCK;
    case UNLOCK;
    case ROOT;
    case CHILD;

    public function value(): int
    {
        return match ($this) {
            self::LOCK => 1,
            self::UNLOCK => 2,
            self::ROOT => 0,
            self::CHILD => 2,
        };
    }
}
