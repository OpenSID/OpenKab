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

    public function listStatistik($kategori): array
    {
        return collect(match ($kategori) {
            'kelas-sosial' => $this->caseKelasSosial(),
            default => []
        })->toArray();
    }

    private function listFooter($dataHeader, $query_footer): array
    {

        $jumlahLakiLaki = $dataHeader->sum('laki_laki');
        $jumlahJerempuan = $dataHeader->sum('perempuan');
        $jumlah = $jumlahLakiLaki + $jumlahJerempuan;


        $totalLakiLaki = $query_footer->sum('laki_laki');
        $totalPerempuan = $query_footer->sum('perempuan');
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

    private function caseKelasSosial(): array
    {
        $kelas = KelasSosial::countStatistik()->get();
        $query = Keluarga::countStatistik()->get();

        return [
            'header' => $kelas,
            'footer' => $this->listFooter($kelas, $query),
        ];
    }
}
