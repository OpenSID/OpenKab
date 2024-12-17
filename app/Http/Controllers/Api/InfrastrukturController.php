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

    public function data()
    {
        $jalan_negara = Komoditas::where('komoditas', '5__497')->first();
        $jalan_provinsi = Komoditas::where('komoditas', '4__491')->first();

        $jalan_baik =
            ($jalan_negara ? $jalan_negara->data['kondisi_baik'] : 0) +
            ($jalan_provinsi ? $jalan_provinsi->data['kondisi_baik'] : 0);

        $jalan_rusak =
        ($jalan_negara ? $jalan_negara->data['kondisi_rusak'] : 0) +
        ($jalan_provinsi ? $jalan_provinsi->data['kondisi_rusak'] : 0);

        $jalan_jumlah = $jalan_baik + $jalan_rusak;

        $jembatan_beton = Komoditas::where('komoditas', '6__503')->first();
        $jembatan_besi = Komoditas::where('komoditas', '6__504')->first();

        // Pastikan kondisi_baik bernilai 0 jika objek tidak ditemukan
        $jembatan_baik =
            ($jembatan_beton ? $jembatan_beton->data['kondisi_baik'] : 0) +
            ($jembatan_besi ? $jembatan_besi->data['kondisi_baik'] : 0);

        $jembatan_rusak =
        ($jembatan_beton ? $jembatan_beton->data['kondisi_rusak'] : 0) +
        ($jembatan_besi ? $jembatan_besi->data['kondisi_rusak'] : 0);

        $jembatan_jumlah = $jembatan_baik + $jembatan_rusak;

        $sanitasi = Potensi::sanitasi()->first();
        $sumur_resapan = $sanitasi ? $sanitasi->data['sumur_resapan'] : '-';
        $mck_umum = $sanitasi ? $sanitasi->data['mck_umum'] : '-';

        $air = Potensi::airBersih()->first();
        $sumur_pompa = $air ? $air->data['air_bersih'] : '-';
        $embung = $air ? $air->data['embung'] : '-';

        $data = [
            ['kategori' => 'Transportasi Darat', 'jenis_sarana' => 'Jalan Raya Aspal', 'kondisi_baik' => $jalan_baik, 'kondisi_rusak' => $jalan_rusak, 'jumlah' => $jalan_jumlah, 'satuan' => 'KM'],
            ['kategori' => 'Transportasi Darat', 'jenis_sarana' => 'Jembatan Besi Beton', 'kondisi_baik' => $jembatan_baik, 'kondisi_rusak' => $jembatan_rusak, 'jumlah' => $jembatan_jumlah, 'satuan' => 'Unit'],
            ['kategori' => 'Sanitasi', 'jenis_sarana' => 'Sumur Resapan', 'kondisi_baik' => $sumur_resapan, 'kondisi_rusak' => '-', 'jumlah' => $sumur_resapan, 'satuan' => 'Unit'],
            ['kategori' => 'Sanitasi', 'jenis_sarana' => 'MCK Umum', 'kondisi_baik' => $mck_umum, 'kondisi_rusak' => '-', 'jumlah' => $mck_umum, 'satuan' => 'Unit'],
            ['kategori' => 'Air Bersih', 'jenis_sarana' => 'Sumur Pompa', 'kondisi_baik' => $sumur_pompa, 'kondisi_rusak' => '-', 'jumlah' => $sumur_pompa, 'satuan' => 'Unit'],
            ['kategori' => 'Air Bersih', 'jenis_sarana' => 'Embung', 'kondisi_baik' => $embung, 'kondisi_rusak' => '-', 'jumlah' => $embung, 'satuan' => 'Unit'],
        ];

        // Ubah nilai 0 menjadi '-'
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

    public function prasaranaSarana()
    {
        return $this->fractal($this->prasarana->index(), new InfrastrukturTransformer(), 'prasarana-sarana')->toArray();
    }
}
