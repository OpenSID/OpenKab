<?php

namespace App\Models\Enums;

use Illuminate\Validation\Rules\Enum;

final class JenisKelaminEnum extends Enum
{
    public const laki_laki = 1;

    public const perempuan = 2;
}
