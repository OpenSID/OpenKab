<?php

namespace App\Http\Repository;

use App\Models\Bantuan;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\Penduduk;

class BantuanRepository
{
    private $filter;

    public function __construct()
    {
        $this->filter = request()->input('filter');
    }

    public function listBantuan()
    {
        $query = Bantuan::query();

        if (session()->has('desa')) {
            $query->where('config_id', session('desa.id'));
        }

        return QueryBuilder::for($query)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'nama',
                'sasaran',
            ])
            ->allowedSorts([
                'nama',
                'sasaran',
            ])
            ->jsonPaginate();
    }

    public function showBantuan()
    {
        $query = Bantuan::query();

        if (session()->has('desa')) {
            $query->where('config_id', session('desa.id'));
        }

        return QueryBuilder::for($query)
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                'nama',
                'sasaran',
            ])
            ->allowedSorts([
                'nama',
                'sasaran',
            ])
            ->first();
    }

    public function listStatistik(): array|object
    {
        return collect(match ($this->filter['id']) {
            'bantuan-penduduk' => $this->caseKategoriPenduduk($this->filter['id'], 1),
            'bantuan-keluarga' => $this->caseKategoriKeluarga($this->filter['id'], 2),
            default => $this->caseBantuan($this->filter['id']),
        })->toArray();
    }

    public function getTotalNonKategori($id, $sasaran = null)
    {
        return Bantuan::when(session()->has('desa'), function ($query) {
            return $query->where('config_id', session('desa.id'));
        })
            ->when($id, function ($query, $id) {
                if (in_array($id, ['bantuan-penduduk', 'bantuan-keluarga'])) {
                    return $query;
                }
                return $query->where('id', $id);
            })
            ->when($sasaran, function ($query, $sasaran) {
                return $query->where('sasaran', $sasaran);
            })
            ->get()
            ->pluck('statistik')
            ->flatten(1)
            ->map(function ($item) {
                return [
                    'jumlah' => $item['laki_laki'] + $item['perempuan'],
                    'laki_laki' => $item['laki_laki'],
                    'perempuan' => $item['perempuan'],
                ];
            });
    }

    public function getTotalKategoriPenduduk($id, $sasaran = null)
    {
        return Bantuan::when(session()->has('desa'), function ($query) {
            return $query->where('config_id', session('desa.id'));
        })
            ->when($id, function ($query, $id) {
                if (in_array($id, ['bantuan-penduduk', 'bantuan-keluarga'])) {
                    return $query;
                }
                return $query->where('id', $id);
            })
            ->when($sasaran, function ($query, $sasaran) {
                return $query->where('sasaran', $sasaran);
            })
            ->get()
            ->pluck('statistik')
            ->flatten(1)
            ->map(function ($item) {
                return [
                    'jumlah' => $item['laki_laki'] + $item['perempuan'],
                    'laki_laki' => $item['laki_laki'],
                    'perempuan' => $item['perempuan'],
                ];
            });
    }

    public function caseKategoriPenduduk($id, $sasaran = null): array
    {
        $header = Bantuan::countStatistikPenduduk()->sasaran()->get();
        $footer = $this->countStatistikKategoriPenduduk();

        return [
            'header' => $header,
            'footer' => $this->listFooter($header, $footer),
        ];
    }

    private function countStatistikKategoriPenduduk(string $whereHeader = null): object
    {
        $query = Penduduk::countStatistik();

        if ($whereHeader) {
            $query->whereRaw($whereHeader);
        }

        return $query->status()->get()->map(function ($item) {
            return [
                'laki_laki' => $item['laki_laki'],
                'perempuan' => $item['perempuan'],
            ];
        });
    }

    // public function caseKategoriKeluarga($id, $sasaran = null): array
    // {
    //     return [
    //         'header' => [],
    //         'footer' => ['keluarga'], // $this->listFooter(),
    //     ];
    // }

    public function caseBantuan($id): array
    {
        $header  = [];
        $bantuan = $this->getTotalNonKategori($id);

        return [
            'header' => [],
            'footer' => $this->listFooter($header, $bantuan),
        ];
    }

    /**
     * @param $dataHeader collection
     * @param $queryFooter collection
     *
     * return array
     */
    private function listFooter($dataHeader, $queryFooter): array|object
    {
        if (count($dataHeader) > 0) {
            $jumlahLakiLaki = $dataHeader->sum('laki_laki');
            $jumlahPerempuan = $dataHeader->sum('perempuan');
            $jumlah = $jumlahLakiLaki + $jumlahPerempuan;


            $totalLakiLaki = $queryFooter[0]['laki_laki'];
            $totalPerempuan = $queryFooter[0]['laki_laki'];
            $total = $totalLakiLaki + $totalPerempuan;

        } else {
            $jumlahLakiLaki = $queryFooter[0]['laki_laki'];
            $jumlahPerempuan = $queryFooter[0]['perempuan'];
            $jumlah = $jumlahLakiLaki + $jumlahPerempuan;

            $totalLakiLaki = $queryFooter[1]['laki_laki'];
            $totalPerempuan = $queryFooter[1]['perempuan'];
            $total = $totalLakiLaki + $totalPerempuan;
        }

        return [
            [
                'nama' => 'Jumlah',
                'jumlah' => $jumlah,
                'laki_laki' => $jumlahLakiLaki,
                'perempuan' => $jumlahPerempuan,
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
}
