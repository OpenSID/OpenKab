<?php

namespace App\Models\Enums;

use Illuminate\Validation\Rules\Enum;

final class StatusEnum extends Enum
{
    const tidakAktif = 0;

    const aktif = 1;
}
