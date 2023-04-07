<?php

namespace App\Http\Repository;

use App\Models\Umur;
use App\Models\Hamil;
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

    public function listStatistik($kategori)
    {
        return match ($kategori) {
            'umur-rentang' => $this->caseUmurRentang(),
            'umur-kategori' => $this->caseUmurKategori(),
            'akta-kelahiran' => $this->caseAktaKelahiran(),
            'hamil' => $this->caseHamil(),
            default => null
        };
    }

    private function listFooter($data_header, $query_footer)
    {
        $data_header  = collect($data_header);
        $query_footer = collect($query_footer);

        if (count($data_header) > 0) {
            $jumlah_laki_laki = $data_header->sum('laki_laki');
            $jumlah_perempuan = $data_header->sum('perempuan');
            $jumlah = $jumlah_laki_laki + $jumlah_perempuan;
        } else {
            $jumlah_laki_laki = 0;
            $jumlah_perempuan = 0;
            $jumlah = 0;
        }

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
        $umur = new Umur();
        $umur->setAppends([]);
        $umur->setWiths([]);
        $umur->countUmurStatistik()->status()->orderBy('id')->get()->toArray();

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
        $umur = Hamil::countStatistik()->where('nama', 'Hamil')->orderBy('id')->get()->toArray();
        $query = Penduduk::countStatistik()->status()->get()->toArray();

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur),
        ];
    }
}
