<?php

namespace App\Http\Repository;

class StatistikRepository
{
    public function getStatistik(array $header = [], array $footer = [])
    {
        $footer = [
            [
                'nama' => 'JUMLAH',
                'jumlah' => 92,
                'laki_laki' => 45,
                'perempuan' => 47,
            ],
            [
                'nama' => 'BELUM MENGISI',
                'jumlah' => 0,
                'laki_laki' => 0,
                'perempuan' => 0,
            ],
            [
                'nama' => 'TOTAL',
                'jumlah' => 92,
                'laki_laki' => 45,
                'perempuan' => 47,
            ]
        ];

        $header = [
            [
                'id' => 1,
                'nama' => 'ISLAM',
                'laki_laki' => 41,
                'perempuan' => 43,
            ],
            [
                'id' => 2,
                'nama' => 'KRISTEN',
                'laki_laki' => 0,
                'perempuan' => 0,
            ],
            [
                'id' => 3,
                'nama' => 'KATHOLIK',
                'laki_laki' => 0,
                'perempuan' => 0,
            ],
            [
                'id' => 4,
                'nama' => 'HINDU',
                'laki_laki' => 3,
                'perempuan' => 4,
            ],
            [
                'id' => 5,
                'nama' => 'BUDHA',
                'laki_laki' => 1,
                'perempuan' => 0,
            ],
            [
                'id' => 6,
                'nama' => 'KHONGHUCU',
                'laki_laki' => 0,
                'perempuan' => 0,
            ],
            [
                'id' => 7,
                'nama' => 'KEPERCAYAAN TERHADAP TUHAN YME / LAINNYA',
                'laki_laki' => 0,
                'perempuan' => 0,
            ]
        ];

        $setFooter = $this->getHitungFooter($footer);

        $setHeader = $this->getHitungHeader($header, $setFooter[0]['jumlah']);

        // $setHitung = collect($hitung)->map(function ($item, $key) {
        //     $item['id'] = $key + 1;
        //     return $item;
        // });

        return collect($setHeader)->merge($setFooter)->toArray();
    }

    private function getHitungHeader(array $kategori = [], int $total = 0)
    {
        return collect($kategori)->map(function ($item, $key) use ($total) {
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
                'laki_laki' => $dataFooter[2]['laki_laki'] - $dataFooter[0]['laki_laki'],
                'perempuan' => $dataFooter[2]['perempuan'] - $dataFooter[0]['perempuan'],
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
