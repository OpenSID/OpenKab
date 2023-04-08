<?php

namespace App\Http\Repository;

use App\Models\Penduduk;

class StatistikRepository
{
    /**
     *
     * return array
     */
    public function getKategoriStatistik($kategori)
    {
        return match ($kategori) {
            'penduduk' => [
                'judul_tabel' => 'Jenis Kelompok',
                'parameter' => 'slug',
                'api' => 'api/penduduk',
                'kategori' => Penduduk::KATEGORI_STATISTIK,
            ],
            'keluarga' => [
                'judul_tabel' => 'Jenis Kelompok',
                'parameter' => 'slug',
                'api' => 'api/keluarga',
                'kategori' => Keluarga::KATEGORI_STATISTIK,
            ],
            'rtm' => [
                'judul_tabel' => 'Jenis Kelompok',
                'parameter' => 'slug',
                'api' => 'api/rtm',
                'kategori' => Rtm::KATEGORI_STATISTIK,
            ],
            'bantuan' => [
                'judul_tabel' => null, // dinamis, diambil dari tabel
                'parameter' => 'slug',
                'api' => 'api/bantuan',
                'kategori' => Bantuan::KATEGORI_STATISTIK,
            ],
        };
    }

    /**
     * @param $data array
     *
     * return array
     */
    public function getStatistik(array $data = [])
    {
        $header = $data['header'] ?? [];
        $footer = $data['footer'] ?? [];

        if (count($footer) > 0) {
            $setFooter = $this->getHitungFooter($footer);

            if (count($header) > 0) {
                $setHeader = $this->getHitungHeader($header, $setFooter[0]['jumlah']);

                $setFooter = collect($setFooter)->map(function ($item, $key) use ($setHeader) {
                    $item['id'] = $key + $setHeader->pluck('id')->max();
                    return $item;
                });

                return $setHeader->merge($setFooter);
            }

            return $setFooter;
        }

        return [];
    }

    private function getHitungHeader(array $dataHeader = [], int $total = 0)
    {
        return collect($dataHeader)->map(function ($item, $key) use ($total) {
            return $this->getPresentase($item, $total);
        });
    }

    private function getHitungFooter(array $dataFooter = [])
    {
        return [
            $this->getPresentase([
                'id'        => 1,
                'nama'      => $dataFooter[0]['nama'],
                'laki_laki' => $dataFooter[0]['laki_laki'],
                'perempuan' => $dataFooter[0]['perempuan'],
            ]),
            $this->getPresentase([
                'id'        => 2,
                'nama'      => $dataFooter[1]['nama'],
                'laki_laki' => $dataFooter[1]['laki_laki'] ?? $dataFooter[2]['laki_laki'] - $dataFooter[0]['laki_laki'],
                'perempuan' => $dataFooter[1]['perempuan'] ?? $dataFooter[2]['perempuan'] - $dataFooter[0]['perempuan'],
            ]),
            $this->getPresentase([
                'id'        => 3,
                'nama'      => $dataFooter[2]['nama'],
                'laki_laki' => $dataFooter[2]['laki_laki'],
                'perempuan' => $dataFooter[2]['perempuan'],
            ]),
        ];
    }

    private function getPresentase(array $data, $pembagi = null)
    {
        $data['jumlah'] = $data['laki_laki'] + $data['perempuan'];
        $pembagi = $pembagi ?? $data['jumlah'];
        $data['persentase_jumlah'] = persen($data['jumlah'], $pembagi);
        $data['persentase_laki_laki'] = persen($data['laki_laki'], $pembagi);
        $data['persentase_perempuan'] = persen($data['perempuan'], $pembagi);

        return $data;
    }
}
