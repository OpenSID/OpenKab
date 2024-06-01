<?php

namespace App\Http\Controllers\Api;

use App\Models\Potensi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SummaryController extends Controller
{
    private $defaultRequest = [
        'luas_wilayah' => 1,
        'luas_pertanian' => 1,
        'luas_perkebunan' => 1,
        'luas_hutan' => 1,
        'luas_peternakan' => 1,
    ];

    private $luasKebun;

    private $luasHutan;

    private $luasTernak;

    public function __invoke(Request $request)
    {
        return response()->json([
            'data' => $this->data($request),
            'message' => 'Berhasil mengambil data summary',
        ], Response::HTTP_OK);
    }

    private function data($request)
    {
        $result = [];
        $userRequest = [];
        if ($request->get('search')) {
            $userRequest = array_intersect($request->get('search'), $this->defaultRequest);
        } else {
            $userRequest = $this->defaultRequest;
        }
        $tahunTerakhir = Potensi::max('tahun');
        if (isset($userRequest['luas_wilayah'])) {
            $result['luas_wilayah'] = $this->getLuasWilayah($tahunTerakhir);
        }
        if (isset($userRequest['luas_pertanian'])) {
            $result['luas_pertanian'] = $this->getLuasPertanian($tahunTerakhir);
        }
        if (isset($userRequest['luas_perkebunan'])) {
            $result['luas_perkebunan'] = $this->getLuasPerkebunan($tahunTerakhir);
        }
        if (isset($userRequest['luas_hutan'])) {
            $result['luas_hutan'] = $this->getLuasHutan($tahunTerakhir);
        }
        if (isset($userRequest['luas_peternakan'])) {
            $result['luas_peternakan'] = $this->getLuasPeternakan($tahunTerakhir);
        }

        return $result;
    }

    private function getLuasWilayah($tahun)
    {
        $total = 0;
        $batasWilayah = Potensi::where('kategori', 'batas-wilayah')->where('tahun', $tahun)->get();
        if (! $batasWilayah->isEmpty()) {
            $batasWilayah->groupBy('config_id')->each(function ($item) use (&$total) {
                $luas = $item->sortByDesc('bulan')->first()->data['luas_desa'];
                $total += money($luas, 'IDR', true)->getValue();
            });
        }

        return $total;
    }

    private function getLuasPertanian($tahun)
    {
        if (! $this->luasHutan) {
            $this->getLuasHutan($tahun);
        }
        if (! $this->luasKebun) {
            $this->getLuasPerkebunan($tahun);
        }
        if (! $this->luasTernak) {
            $this->getLuasPeternakan($tahun);
        }

        return $this->luasHutan + $this->luasKebun + $this->luasTernak;
    }

    private function getLuasPerkebunan($tahun)
    {
        $total = 0;
        if ($this->luasKebun) {
            return $this->luasKebun;
        }
        $batasWilayah = Potensi::where('kategori', 'kepemilikan-lahan-kebun')->where('tahun', $tahun)->get();
        if (! $batasWilayah->isEmpty()) {
            $batasWilayah->groupBy('config_id')->each(function ($item) use (&$total) {
                $luas1 = $item->sortByDesc('bulan')->first()->data['memiliki_kurang_10_ha'];
                $luas2 = $item->sortByDesc('bulan')->first()->data['memiliki_10_50_ha'];
                $luas3 = $item->sortByDesc('bulan')->first()->data['memiliki_50_100_ha'];
                $luas4 = $item->sortByDesc('bulan')->first()->data['memiliki_lebih_dari_100_ha'];
                $total += money($luas1, 'IDR', true)->getValue() + money($luas2, 'IDR', true)->getValue() + money($luas3, 'IDR', true)->getValue() + money($luas4, 'IDR', true)->getValue();
            });
        }
        $this->luasKebun = $total;

        return $total;
    }

    private function getLuasHutan($tahun)
    {
        $total = 0;
        if ($this->luasHutan) {
            return $this->luasHutan;
        }
        $batasWilayah = Potensi::where('kategori', 'kepemilikan-lahan-hutan')->where('tahun', $tahun)->get();
        if (! $batasWilayah->isEmpty()) {
            $batasWilayah->groupBy('config_id')->each(function ($item) use (&$total) {
                $luas = $item->sortByDesc('bulan')->first()->data['jumlah_luas_hutan'];
                $total += money($luas, 'IDR', true)->getValue();
            });
        }
        $this->luasHutan = $total;

        return $total;
    }

    private function getLuasPeternakan($tahun)
    {
        $total = 0;
        if ($this->luasTernak) {
            return $this->luasTernak;
        }
        $batasWilayah = Potensi::where('kategori', 'lahan-dan-pakan-ternak')->where('tahun', $tahun)->get();
        if (! $batasWilayah->isEmpty()) {
            $batasWilayah->groupBy('config_id')->each(function ($item) use (&$total) {
                $luas = $item->sortByDesc('bulan')->first()->data['luas_lahan_gembalan'];
                $total += money($luas, 'IDR', true)->getValue();
            });
        }
        $this->luasTernak = $total;

        return $total;
    }
}
