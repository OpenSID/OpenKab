<?php

namespace App\Http\Repository;

use App\Models\Rtm;
use App\Models\Bantuan;
use App\Models\Kelompok;
use App\Models\Keluarga;
use App\Models\Penduduk;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class BantuanRepository
{
    public function listBantuan()
    {
        $query = Bantuan::configId();

        return QueryBuilder::for($query)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('nama', 'LIKE', '%' . $value . '%')
                        ->orWhere('asaldana', 'LIKE', '%' . $value . '%');
                }),
            ])
            ->allowedSorts([
                'nama',
                'asaldana',
            ])
            ->jsonPaginate();
    }

    public function showBantuan()
    {
        $query = Bantuan::configId();

        return QueryBuilder::for($query)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
            ])
            ->first();
    }

    public function listStatistik($kategori): array
    {
        return collect(match ($kategori) {
            'penduduk' => $this->caseKategoriPenduduk(),
            'keluarga' => $this->caseKategoriKeluarga(),
            default => $this->caseNonKategori($kategori),
        })->toArray();
    }

    public function getBantuanNonKategori($id): array
    {
        $bantuan = Bantuan::configId()->whereId($id)->first();

        return [
            [
                'nama' => 'PESERTA',
                'laki_laki' => $bantuan->statistik['laki_laki'],
                'perempuan' => $bantuan->statistik['perempuan'],
            ],
            $this->getTotal($bantuan->sasaran),
        ];
    }

    private function getTotal($sasaran): array
    {
        $total = match ($sasaran) {
            Bantuan::SASARAN_PENDUDUK => $this->countStatistikKategoriPenduduk(),
            Bantuan::SASARAN_KELUARGA => $this->countStatistikKategoriKeluarga(),
            Bantuan::SASARAN_RUMAH_TANGGA => $this->countStatistikKategoriRtm(),
            Bantuan::SASARAN_KELOMPOK => $this->countStatistikKategoriKelompok(),
            default => [],
        };

        return [
            'laki_laki' => $total[0]['laki_laki'] ?? 0,
            'perempuan' => $total[0]['perempuan'] ?? 0,
        ];
    }

    public function caseKategoriPenduduk(): array
    {
        $header = Bantuan::configId()->countStatistikPenduduk()->get();
        $footer = $this->countStatistikKategoriPenduduk();

        return [
            'header' => $header,
            'footer' => $this->listFooter($header, $footer),
        ];
    }

    private function countStatistikKategoriPenduduk(): object
    {
        return Penduduk::configId()->countStatistik()->status()->get();
    }

    public function caseKategoriKeluarga(): array
    {
        $header = Bantuan::configId()->countStatistikKeluarga()->get();
        $footer = $this->countStatistikKategoriKeluarga();

        return [
            'header' => $header,
            'footer' => $this->listFooter($header, $footer),
        ];
    }

    private function countStatistikKategoriKeluarga(): object
    {
        return Keluarga::configId()->countStatistik()->status()->get();
    }

    private function countStatistikKategoriRtm(): object
    {
        return Rtm::configId()->countStatistik()->status()->get();
    }

    private function countStatistikKategoriKelompok(): object
    {
        return Kelompok::configId()->countStatistik()->status()->get();
    }

    public function caseNonKategori($id): array
    {
        $header  = [];
        $bantuan = $this->getBantuanNonKategori($id);

        return [
            'header' => $header,
            'footer' => $this->listFooter($header, $bantuan),
        ];
    }

    /**
     * @param $dataHeader collection
     * @param $queryFooter collection
     *
     * return array
     */
    private function listFooter($dataHeader, $queryFooter): array
    {
        if (count($dataHeader) > 0) {
            $jumlahLakiLaki = $dataHeader->sum('laki_laki');
            $jumlahPerempuan = $dataHeader->sum('perempuan');
            $jumlah = $jumlahLakiLaki + $jumlahPerempuan;


            $totalLakiLaki = $queryFooter[0]['laki_laki'];
            $totalPerempuan = $queryFooter[0]['perempuan'];
            $total = $totalLakiLaki + $totalPerempuan;

        } else {
            $jumlahLakiLaki = $queryFooter[0]['laki_laki'] ?? 0;
            $jumlahPerempuan = $queryFooter[0]['perempuan'] ?? 0;
            $jumlah = $jumlahLakiLaki + $jumlahPerempuan;

            $totalLakiLaki = $queryFooter[1]['laki_laki'] ?? 0;
            $totalPerempuan = $queryFooter[1]['perempuan'] ?? 0;
            $total = $totalLakiLaki + $totalPerempuan;
        }

        return [
            [
                'nama' => 'Peserta',
                'jumlah' => $jumlah,
                'laki_laki' => $jumlahLakiLaki,
                'perempuan' => $jumlahPerempuan,
            ],
            [
                'nama' => 'Bukan Peserta',
            ],
            [
                'nama' => 'Total',
                'jumlah' => $total,
                'laki_laki' => $totalLakiLaki,
                'perempuan' => $totalPerempuan,
            ],
        ];
    }
}
