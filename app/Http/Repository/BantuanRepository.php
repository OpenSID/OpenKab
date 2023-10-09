<?php

namespace App\Http\Repository;

use App\Models\Bantuan;
use App\Models\Kelompok;
use App\Models\Keluarga;
use App\Models\Rtm;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BantuanRepository
{
    public function listBantuan()
    {
        return  QueryBuilder::for(Bantuan::filterWilayah())
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('sasaran'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('nama', 'LIKE', '%'.$value.'%')
                        ->orWhere('asaldana', 'LIKE', '%'.$value.'%');
                }),
                AllowedFilter::callback('tahun', function ($query, $value) {
                    $query->whereYear('sdate', '<=', $value)
                        ->whereYear('edate', '>=', $value);
                }),

            ])
            ->allowedSorts([
                'nama',
                'asaldana',
            ])->jsonPaginate();
    }

    public function cetakListBantuan()
    {
        return  QueryBuilder::for(Bantuan::filterWilayah())
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('sasaran'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('nama', 'LIKE', '%'.$value.'%')
                        ->orWhere('asaldana', 'LIKE', '%'.$value.'%');
                }),
                AllowedFilter::callback('tahun', function ($query, $value) {
                    $query->whereYear('sdate', '<=', $value)
                        ->whereYear('edate', '>=', $value);
                }),

            ])->get();
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
        $bantuan = Bantuan::whereId($id)->first();

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
        $header = Bantuan::countStatistikPenduduk()->get();
        $footer = $this->countStatistikKategoriPenduduk();

        return [
            'header' => $header,
            'footer' => $this->listFooter($header, $footer),
        ];
    }

    private function countStatistikKategoriPenduduk(): object
    {
        $configDesa = request('config_desa') ?? null;

        $bantuan = new Bantuan();
        // if (! isset(request('filter')['tahun']) && ! isset(request('filter')['bulan'])) {
        //     $bantuan->status();
        // }

        if ($configDesa) {
            $bantuan->where(function ($q) use ($configDesa) {
                return $q->where('program.config_id', $configDesa)->orWhereNull('program.config_id');
            });
        }

        return $bantuan->get();
    }

    public function caseKategoriKeluarga(): array
    {
        $header = Bantuan::countStatistikKeluarga()->get();
        $footer = $this->countStatistikKategoriKeluarga();

        return [
            'header' => $header,
            'footer' => $this->listFooter($header, $footer),
        ];
    }

    private function countStatistikKategoriKeluarga(): object
    {
        return Keluarga::countStatistik()->status()->get();
    }

    private function countStatistikKategoriRtm(): object
    {
        return Rtm::countStatistik()->status()->get();
    }

    private function countStatistikKategoriKelompok(): object
    {
        return Kelompok::countStatistik()->status()->get();
    }

    public function caseNonKategori($id): array
    {
        $header = [];
        $bantuan = $this->getBantuanNonKategori($id);

        return [
            'header' => $header,
            'footer' => $this->listFooter($header, $bantuan),
        ];
    }

    /**
     * @param $dataHeader  collection
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

    public function tahun()
    {
        return Bantuan::when(request('id'), function ($query) {
            collect(match (request('id')) {
                'penduduk' => $query->where('sasaran', 1),
                'keluarga' => $query->where('sasaran', 2),
                default => $query->where('id', request('id')),
            });
        })->minMaxTahun('sdate')->first();
    }

    public function listBantuanKabupaten()
    {
        return QueryBuilder::for(Bantuan::class)
            ->whereNull('config_id')
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('sasaran'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('nama', 'LIKE', '%'.$value.'%')
                        ->orWhere('asaldana', 'LIKE', '%'.$value.'%');
                }),
                AllowedFilter::callback('tahun', function ($query, $value) {
                    $query->whereYear('sdate', '<=', $value)
                        ->whereYear('edate', '>=', $value);
                }),

            ])
            ->allowedSorts([
                'nama',
                'asaldana',
            ])->jsonPaginate();
    }

    public function summary()
    {
        return QueryBuilder::for(Bantuan::class)->count();
    }
}
