<?php

namespace App\Http\Repository;

class StatistikRepository
{
    public function getStatistik(array $header = [], array $footer = [])
    {
        $setFooter = $this->getHitungFooter($footer);

        if (count($header) > 0) {
            $setFooter = collect($setFooter)->map(function ($item, $key) use ($header) {
                $item['id'] = $key + 1 + count($header);
                return $item;
            });

            $setHeader = $this->getHitungHeader($header, $setFooter[0]['jumlah']);

            return [... $setHeader, ... $setFooter];
        }

        return $setFooter;
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
