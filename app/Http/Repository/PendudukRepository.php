<?php

namespace App\Http\Repository;

use App\Models\Umur;
use App\Models\Penduduk;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class PendudukRepository
{
    public function listPenduduk()
    {
        return QueryBuilder::for(Penduduk::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('keluarga.no_kk'),
                'nama',
                'nik',
                'tag_id_card',
            ])
            ->allowedSorts([
                'nik',
                'nama',
                'umur',
                'created_at',
            ])
            ->jsonPaginate();
    }

    public function listStatistik()
    {
        return match (request()->input('filter')['slug']) {
            'umur-rentang' => $this->caseUmurRentang(),
            'umur-kategori' => $this->caseUmurKategori(),
            'akta-kelahiran' => $this->caseAktaKelahiran(),
            'hamil' => [
                'header' => Hamil::countStatistik()->status()->orderBy('id')->get()->toArray(),
                'footer' => $this->contohFooter(),
            ],
            default => null
        };
    }

    private function listFooter($dataHeader, $queryFooter)
    {
        if (count($dataHeader) > 0) {
            $jumlah_laki_laki = $dataHeader->sum('laki_laki');
            $jumlah_perempuan = $dataHeader->sum('perempuan');
            $jumlah = $jumlah_laki_laki + $jumlah_perempuan;
        } else {
            $jumlah_laki_laki = 0;
            $jumlah_perempuan = 0;
            $jumlah = 0;
        }

        $total_laki_laki = $queryFooter->sum('laki_laki');
        $total_perempuan = $queryFooter->sum('perempuan');
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
                'jumlah' => 0,
                'laki_laki' => 0,
                'perempuan' => 0,
            ],
            [
                'nama' => 'Total',
                'jumlah' => $total,
                'laki_laki' => $total_laki_laki,
                'perempuan' => $total_perempuan,
            ],
        ];
    }

    // Umur Rentang
    private function caseUmurRentang()
    {
        $umur = Umur::countUmurStatistik()->status()->orderBy('id')->get()->toArray();
        $query = Penduduk::countStatistik()->status()->get()->toArray();

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur, $query),
        ];
    }

    // Umur Kategori
    private function caseUmurKategori()
    {
        $umur = Umur::countUmurStatistik()->status()->orderBy('id')->get()->toArray();

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur),
        ];
    }

    // Akta Kelahiran
    private function caseAktaKelahiran()
    {
        $umur = Umur::countAktaStatistik()->status()->orderBy('id')->get()->toArray();

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur),
        ];
    }

    // Hamil
    private function caseHamil()
    {
        $umur = Umur::countStatistik()->status()->orderBy('id')->get()->toArray();
        $query = Penduduk::countStatistik()->status()->get()->toArray();

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur),
        ];
    }
}
