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
            'grafik_penduduk' => $this->grafikPenduduk(),
        ];
    }

    private function grafikPenduduk()
    {
        // ambil tahun terakhir dari data penduduk
        $tahun_awal = Penduduk::selectRaw('YEAR(created_at) as tahun')
            ->status()
            ->orderBy('tahun', 'asc')
            ->first();

        // menampilkan data penduduk berdasarkan bulan dan tahun berjalan, data yang akan ditampilkan adalah data 12 bulan terakhir dan berlanjut dari bulan sebelumnya
        $penduduk = Penduduk::selectRaw('count(*) as jumlah, MONTH(created_at) as bulan, YEAR(created_at) as tahun')
            ->status()
            ->groupBy('bulan', 'tahun')
            ->orderBy('tahun', 'asc')
            ->orderBy('bulan', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'jumlah' => $item->jumlah,
                    'bulan' => $item->bulan,
                    'tahun' => $item->tahun,
                ];
            });

        // return $penduduk;

        // tampilkan data dari tahun_awal sampai saat ini secara continue dengan bulannya
        $data = [];
        // force dari tahun_awal sampai tahun sekarang
        $tahun_awal = $tahun_awal->tahun;
        $tahun_akhir = now()->year;

        for ($i = $tahun_awal; $i <= $tahun_akhir; $i++) {
            $data[$i] = [
                'Januari' => 0,
                'Februari' => 0,
                'Maret' => 0,
                'April' => 0,
                'Mei' => 0,
                'Juni' => 0,
                'Juli' => 0,
                'Agustus' => 0,
                'September' => 0,
                'Oktober' => 0,
                'November' => 0,
                'Desember' => 0,
            ];
        }

        // ganti data bulan dengan data yang ada di database
        // dengan konsisi
        // 1. data berdasarkan data sebelumnya + data tambahan sekarang
        $data = $penduduk->reduce(function ($carry, $item) use ($data) {
            $data[$item['tahun']][bulan($item['bulan'])] = $item['jumlah'];
            return $data;
        }, $data);

        // $data = $penduduk->reduce(function ($carry, $item) use ($data) {
        //     $data[$item['tahun']][bulan($item['bulan'])] = $item['jumlah'];
        //     return $data;
        // }, $data);

        // jika data bulan sebelumnya kosong, maka data bulan sebelumnya diisi dengan data bulan sekarang
        // $tahun = now()->subYears(12)->year;
        // for ($i = 0; $i < 12; $i++) {
        //     $data[$tahun] = array_reduce($data[$tahun], function ($carry, $item) use ($data, $tahun) {
        //         if ($item == 0) {
        //             $item = $carry;
        //         }
        //         $carry[] = $item;
        //         return $carry;
        //     }, []);
        //     $tahun++;
        // }

        return $data;
    }
}
