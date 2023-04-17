<?php

namespace App\Http\Repository;

use App\Models\Rtm;
use App\Models\Bantuan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\KelasSosial;
use Spatie\QueryBuilder\QueryBuilder;
use App\Models\Enums\JenisKelaminEnum;
use Spatie\QueryBuilder\AllowedFilter;

class DasborRepository
{
    public function listDasbor()
    {
        return [
            'jumlah_penduduk_laki_laki' => Penduduk::status()->jenisKelamin(JenisKelaminEnum::laki_laki)->count(),
            'jumlah_penduduk_perempuan' => Penduduk::status()->jenisKelamin(JenisKelaminEnum::perempuan)->count(),
            'jumlah_penduduk' => Penduduk::status()->count(),
            'jumlah_keluarga' => Keluarga::status()->count(),
            'jumlah_rtm' => Rtm::status()->count(),
            'jumlah_bantuan' => Bantuan::count(),
        ];
    }
}
