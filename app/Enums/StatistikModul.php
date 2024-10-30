<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\Bantuan;
use App\Models\Keluarga;
use BenSampo\Enum\Enum;
use App\Models\Penduduk;
use App\Models\Rtm;

final class StatistikModul extends Enum
{
    public const KEPENDUDUKAN    = 1;
    public const KELUARGA      = 2;
    public const RTM = 3;
    public const BANTUAN = 4;

    public static function getPenduduk(): array
    {
        return Penduduk::KATEGORI_STATISTIK;
    }

    public static function getKeluarga(): array
    {
        return Keluarga::KATEGORI_STATISTIK;
    }

    public static function getRtm(): array
    {
        return Rtm::KATEGORI_STATISTIK;
    }

    public static function getBantuan(): array
    {
        return Bantuan::KATEGORI_STATISTIK;
    }
}
