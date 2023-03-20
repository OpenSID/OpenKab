<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static TidakAktif()
 * @method static static Aktif()
 */
final class Status extends Enum
{
    const TidakAktif = 0;
    const Aktif = 1;
}
