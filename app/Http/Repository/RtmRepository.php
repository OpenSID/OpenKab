<?php

namespace App\Http\Repository;

use App\Models\Rtm;

class RtmRepository
{
    public function listStatistik($kategori): array|object
    {
        return collect(match ($kategori) {
            'bdt' => $this->caseBdt(),
            default => []
        })->toArray();
    }

    public function listTahun()
    {
        return Rtm::minMaxTahun('tgl_daftar')->first();
    }

    private function listFooter($dataHeader, $queryFooter): array|object
    {
        $jumlahLakiLaki = $dataHeader->sum('laki_laki');
        $jumlahJerempuan = $dataHeader->sum('perempuan');
        $jumlah = $jumlahLakiLaki + $jumlahJerempuan;

        $totalLakiLaki = $queryFooter->sum('laki_laki');
        $totalPerempuan = $queryFooter->sum('perempuan');
        $total = $totalLakiLaki + $totalPerempuan;

        return [
            [
                'nama' => 'Jumlah',
                'jumlah' => $jumlah,
                'laki_laki' => $jumlahLakiLaki,
                'perempuan' => $jumlahJerempuan,
            ],
            [
                'nama' => 'Belum Mengisi',
            ],
            [
                'nama' => 'Total',
                'jumlah' => $total,
                'laki_laki' => $totalLakiLaki,
                'perempuan' => $totalPerempuan,
            ],
        ];
    }

    private function caseBdt(): array|object
    {
        $bdt = Rtm::CountStatistik()->filters(request()->input('filter'), 'tgl_daftar');
        $queryFooter = $bdt->get();
        $dataHeader = $bdt->bdt(true)->get();

        return [
            'header' => [],
            'footer' => $this->listFooter($dataHeader, $queryFooter),
        ];
    }
}
