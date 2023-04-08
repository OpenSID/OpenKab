<?php

namespace App\Http\Repository;

use App\Models\Keluarga;
use App\Models\KelasSosial;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class KeluargaRepository
{
    public function listKeluarga()
    {
        return QueryBuilder::for(Keluarga::class)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'no_kk',
                'nik_kepala',
                'kelas_sosial',
            ])
            ->allowedSorts([
                'no_kk',
                'nik_kepala',
                'kelas_sosial',
                'created_at',
            ])
            ->jsonPaginate();
    }

    /**
     * @param $kategori string
     *
     * return array
     */
    public function listStatistik($kategori)
    {
        return collect(match ($kategori) {
            'kelas-sosial' => $this->caseKelasSosial(),
            default => []
        })->toArray();
    }

    /**
     * @param $data_header collection
     * @param $query_footer collection
     *
     * return array
     */
    private function listFooter($data_header, $query_footer)
    {

        $jumlah_laki_laki = $data_header->sum('laki_laki');
        $jumlah_perempuan = $data_header->sum('perempuan');
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
            ],
            [
                'nama' => 'Total',
                'jumlah' => $total,
                'laki_laki' => $total_laki_laki,
                'perempuan' => $total_perempuan,
            ],
        ];
    }

    /**
     * Kelas Sosial
     *
     * return array
     */
    private function caseKelasSosial()
    {
        $kelas = KelasSosial::countStatistik()->get();
        $query = Keluarga::countStatistik()->get();

        return [
            'header' => $kelas,
            'footer' => $this->listFooter($kelas, $query),
        ];
    }
}
