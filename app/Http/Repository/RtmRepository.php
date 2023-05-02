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

    public function listFooter(): array|object
    {
        $rtm = Rtm::countStatistik();

        $jumlah = $rtm->bdt(true)->get();
        $jumlah_laki_laki = $jumlah->sum('laki_laki');
        $jumlah_perempuan = $jumlah->sum('perempuan');
        $jumlah = $jumlah_laki_laki + $jumlah_perempuan;

        $total = $rtm->get();
        $total_laki_laki = $total->sum('laki_laki');
        $total_perempuan = $total->sum('perempuan');
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
        $bdt = RTM::countStatistik()->get();
        $query = RTM::configId()->countStatistik()->get();
        $query = RTM::configId()->countStatistik()->where('tgl_daftar', function ($q) {
            if (isset(request('filter')['tahun'])) {
                $q->whereYear('tgl_daftar', '<=', request('filter')['tahun']);
            }

            if (isset(request('filter')['bulan'])) {
                $q->whereMonth('tgl_daftar', '<=', request('filter')['bulan']);
            }
        });

        if (!isset(request('filter')['tahun']) && !isset(request('filter')['bulan'])) {
            $query->status();
        }

        $query->get();

        return [
            'header' => $bdt,
            'footer' => $this->listFooter($bdt, $query),
        ];
    }
}
