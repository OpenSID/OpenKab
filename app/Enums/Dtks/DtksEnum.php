<?php

namespace App\Enums\Dtks;

class DtksEnum
{
    public const VERSION_CODE = 2;

    public const REGSOS_EK2021_RT = 1;

    public const REGSOS_EK2022_K = 2;

    public const VERSION_LIST = [
        self::REGSOS_EK2021_RT => 'REGSOS-EK2021.RT',
        self::REGSOS_EK2022_K => 'REGSOSEK2022.K',
    ];

    final public static function GET_CLEAN_NAME_VERSION($code = self::VERSION_CODE): string
    {
        // remove char (-) and (.)
        return strtoupper(str_replace(['-', '.'], '', self::VERSION_LIST[$code]));
    }
}
