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
        return Rtm::tahun()->first();
    }

    public function listFooter($dataHeader, $query_footer): array|object
    {
        $jumlah_laki_laki = $dataHeader->sum('laki_laki');
        $jumlah_perempuan = $dataHeader->sum('perempuan');
        $jumlah = $jumlah_laki_laki + $jumlah_perempuan;

        $total_laki_laki = $query_footer->sum('laki_laki');
        $total_perempuan = $query_footer->sum('perempuan');
        $total = $total_laki_laki + $total_perempuan;

        return [
            [
                'nama' => 'Jumlah',
                'jumlah' => $jumlah,
                'laki_laki' => $jumlah_laki_laki,
                'perempuan' => $jumlah_perempuan,
            ],
            [
                'nama' => 'Belum Mengisi',
                'jumlah' => $total - $jumlah,
                'laki_laki' => $total_laki_laki - $jumlah_laki_laki,
                'perempuan' => $total_perempuan - $jumlah_perempuan,
            ],
            [
                'nama' => 'Total',
                'jumlah' => $total,
                'laki_laki' => $total_laki_laki,
                'perempuan' => $total_perempuan,
            ],
        ];
    }

    private function caseBdt(): array|object
    {
        $bdt = Rtm::CountStatistik();
        $dataHeader = $bdt->bdt(true)->get();
        $query_footer = $bdt->filters(request()->input('filter'), 'tgl_daftar')->get();

        return [
            'header' => $bdt,
            'footer' => $this->listFooter($dataHeader, $query_footer),
        ];
    }
}
