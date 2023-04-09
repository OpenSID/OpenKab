<?php

namespace App\Http\Repository;

use App\Models\Bantuan;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

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

    public function getQuery($id, $sasaran = null)
    {
        return Bantuan::query()
            ->when(session()->has('desa'), function ($query) {
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

    // public function caseKategoriPenduduk($id, $sasaran = null): array
    // {
    //     return [
    //         'header' => [],
    //         'footer' => $this->listFooter($id, $sasaran),
    //     ];
    // }

    // public function caseKategoriKeluarga($id, $sasaran = null): array
    // {
    //     return [
    //         'header' => [],
    //         'footer' => ['keluarga'], // $this->listFooter(),
    //     ];
    // }

    public function caseBantuan($id): array
    {
        $bantuan = $this->getQuery($id);

        return [
            'header' => $bantuan[0],
            'footer' => $this->listFooter($bantuan[0], $bantuan[0]),
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
        $queryFooter = collect($queryFooter);

        $total_laki_laki = $queryFooter->sum('laki_laki');
        $total_perempuan = $queryFooter->sum('perempuan');
        $total = $total_laki_laki + $total_perempuan;

        return [
            // [
            //     'nama' => 'Jumlah',
            //     'jumlah' => $jumlah,
            //     'laki_laki' => $jumlah_laki_laki,
            //     'perempuan' => $jumlah_perempuan,
            // ],
            // [
            //     'nama' => 'Belum Mengisi',
            //     'jumlah' => $total - $jumlah,
            //     'laki_laki' => $total_laki_laki - $jumlah_laki_laki,
            //     'perempuan' => $total_perempuan - $jumlah_perempuan,
            // ],
            [
                'nama' => 'Total',
                'jumlah' => $total,
                'laki_laki' => $total_laki_laki,
                'perempuan' => $total_perempuan,
            ],
        ];

    }
}
