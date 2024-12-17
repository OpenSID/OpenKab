<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\InfrastrukturRepository;
use App\Http\Transformers\InfrastrukturTransformer;
use App\Models\Komoditas;
use App\Models\Potensi;

class InfrastrukturController extends Controller
{
    public function __construct(protected InfrastrukturRepository $prasarana)
    {
    }

    /**
     * Mengambil data infrastruktur: jalan, jembatan, sanitasi, dan air bersih.
     */
    public function data()
    {
        // Data Jalan Raya Aspal
        $jalanNegara = Komoditas::where('komoditas', '5__497')->first();
        $jalanProvinsi = Komoditas::where('komoditas', '4__491')->first();

        $jalanBaik = ($jalanNegara ? $jalanNegara->data['kondisi_baik'] : 0) +
                     ($jalanProvinsi ? $jalanProvinsi->data['kondisi_baik'] : 0);

        $jalanRusak = ($jalanNegara ? $jalanNegara->data['kondisi_rusak'] : 0) +
                      ($jalanProvinsi ? $jalanProvinsi->data['kondisi_rusak'] : 0);

        $jalanJumlah = $jalanBaik + $jalanRusak;

        // Data Jembatan Besi Beton
        $jembatanBeton = Komoditas::where('komoditas', '6__503')->first();
        $jembatanBesi = Komoditas::where('komoditas', '6__504')->first();

        $jembatanBaik = ($jembatanBeton ? $jembatanBeton->data['kondisi_baik'] : 0) +
                        ($jembatanBesi ? $jembatanBesi->data['kondisi_baik'] : 0);

        $jembatanRusak = ($jembatanBeton ? $jembatanBeton->data['kondisi_rusak'] : 0) +
                         ($jembatanBesi ? $jembatanBesi->data['kondisi_rusak'] : 0);

        $jembatanJumlah = $jembatanBaik + $jembatanRusak;

        // Data Sanitasi
        $sanitasi = Potensi::sanitasi()->first();
        $sumurResapan = $sanitasi ? $sanitasi->data['sumur_resapan'] : '-';
        $mckUmum = $sanitasi ? $sanitasi->data['mck_umum'] : '-';

        // Data Air Bersih
        $airBersih = Potensi::airBersih()->first();
        $sumurPompa = $airBersih ? $airBersih->data['air_bersih'] : '-';
        $embung = $airBersih ? $airBersih->data['embung'] : '-';

        // Data Infrastruktur
        $data = [
            [
                'kategori'       => 'Transportasi Darat',
                'jenis_sarana'   => 'Jalan Raya Aspal',
                'kondisi_baik'   => $jalanBaik,
                'kondisi_rusak'  => $jalanRusak,
                'jumlah'         => $jalanJumlah,
                'satuan'         => 'KM'
            ],
            [
                'kategori'       => 'Transportasi Darat',
                'jenis_sarana'   => 'Jembatan Besi Beton',
                'kondisi_baik'   => $jembatanBaik,
                'kondisi_rusak'  => $jembatanRusak,
                'jumlah'         => $jembatanJumlah,
                'satuan'         => 'Unit'
            ],
            [
                'kategori'       => 'Sanitasi',
                'jenis_sarana'   => 'Sumur Resapan',
                'kondisi_baik'   => $sumurResapan,
                'kondisi_rusak'  => '-',
                'jumlah'         => $sumurResapan,
                'satuan'         => 'Unit'
            ],
            [
                'kategori'       => 'Sanitasi',
                'jenis_sarana'   => 'MCK Umum',
                'kondisi_baik'   => $mckUmum,
                'kondisi_rusak'  => '-',
                'jumlah'         => $mckUmum,
                'satuan'         => 'Unit'
            ],
            [
                'kategori'       => 'Air Bersih',
                'jenis_sarana'   => 'Sumur Pompa',
                'kondisi_baik'   => $sumurPompa,
                'kondisi_rusak'  => '-',
                'jumlah'         => $sumurPompa,
                'satuan'         => 'Unit'
            ],
            [
                'kategori'       => 'Air Bersih',
                'jenis_sarana'   => 'Embung',
                'kondisi_baik'   => $embung,
                'kondisi_rusak'  => '-',
                'jumlah'         => $embung,
                'satuan'         => 'Unit'
            ],
        ];

        // Mengubah nilai 0 menjadi '-'
        $data = array_map(function ($item) {
            foreach ($item as $key => $value) {
                if ($value === 0 || $value === '0') {
                    $item[$key] = '-';
                }
            }
            return $item;
        }, $data);

        return response()->json($data);
    }

    /**
     * Mengambil data prasarana dan sarana.
     */
    public function prasaranaSarana()
    {
        return $this->fractal(
            $this->prasarana->index(),
            new InfrastrukturTransformer(),
            'prasarana-sarana'
        )->toArray();
    }
}
