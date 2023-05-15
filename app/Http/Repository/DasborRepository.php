<?php

namespace App\Http\Repository;

use App\Models\Bantuan;
use App\Models\Enums\JenisKelaminEnum;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rtm;

class DasborRepository
{
    public function listDasbor()
    {
        return [
            'jumlah_penduduk_laki_laki' => Penduduk::status()->FilterKecamatan()->FilterDesa()->jenisKelamin(JenisKelaminEnum::laki_laki)->count(),
            'jumlah_penduduk_perempuan' => Penduduk::status()->FilterKecamatan()->FilterDesa()->jenisKelamin(JenisKelaminEnum::perempuan)->count(),
            'jumlah_penduduk' => Penduduk::status()->FilterKecamatan()->FilterDesa()->count(),
            'jumlah_keluarga' => Keluarga::status()->FilterKecamatan()->FilterDesa()->count(),
            'jumlah_rtm' => Rtm::status()->FilterKecamatan()->FilterDesa()->count(),
            'jumlah_bantuan' => Bantuan::count(),
            'grafik_penduduk' => $this->grafikPenduduk(),
        ];
    }

    private function grafikPenduduk()
    {
        $batas_bawah = 12;

        $penduduk = Penduduk::selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->selectRaw('MONTH(tweb_penduduk.created_at) as bulan')
            ->selectRaw('YEAR(tweb_penduduk.created_at) as tahun')
            ->status()
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->FilterKecamatan()
            ->FilterDesa()
            ->get()
            ->map(function ($item) {
                return [
                    'tahun' => (int) $item->tahun,
                    'bulan' => (int) $item->bulan,
                    'laki_laki' => (int) $item->laki_laki,
                    'perempuan' => (int) $item->perempuan,
                ];
            });

        $data = [];
        for ($i = 0; $i < $batas_bawah; $i++) {
            $tahun = (int) now()->subMonths($i)->format('Y');
            $bulan = (int) now()->subMonths($i)->format('m');

            $laki_laki = $penduduk->filter(function ($item) use ($tahun, $bulan) {
                return $item['tahun'] < $tahun || ($item['tahun'] == $tahun && $item['bulan'] <= $bulan);
            })->sum('laki_laki');

            $perempuan = $penduduk->filter(function ($item) use ($tahun, $bulan) {
                return $item['tahun'] < $tahun || ($item['tahun'] == $tahun && $item['bulan'] <= $bulan);
            })->sum('perempuan');

            $data[] = [
                'kategori' => bulan($bulan).' '.$tahun,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'laki_laki' => $laki_laki,
                'perempuan' => $perempuan,
            ];
        }

        $data = collect($data)->reverse();

        return [
            'kategori' => $data->pluck('kategori'),
            'laki_laki' => $data->pluck('laki_laki'),
            'perempuan' => $data->pluck('perempuan'),
        ];
    }
}
