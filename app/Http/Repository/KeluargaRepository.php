<?php

namespace App\Http\Repository;

use App\Models\KelasSosial;
use App\Models\Keluarga;
use App\Models\LogKeluarga;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

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

    public function rincianKeluarga(string $no_kk)
    {
        return Keluarga::where('no_kk', $no_kk)->get();
    }

    public function listStatistik($kategori): array|object
    {
        return collect(match ($kategori) {
            'kelas-sosial' => $this->caseKelasSosial(),
            default => []
        })->toArray();
    }

    public function listTahun()
    {
        return LogKeluarga::tahun()->first();
    }

    private function listFooter($dataHeader, $query_footer): array|object
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
                'jumlah' => $total - $jumlah,
                'laki_laki' => $totalLakiLaki - $jumlahLakiLaki,
                'perempuan' => $totalPerempuan - $jumlahJerempuan,
            ],
            [
                'nama' => 'Total',
                'jumlah' => $total,
                'laki_laki' => $totalLakiLaki,
                'perempuan' => $totalPerempuan,
            ],
        ];
    }

    private function caseKelasSosial(): array|object
    {
        $kelas = KelasSosial::countStatistik()->get();
        $query = Keluarga::configId()->countStatistik()->whereHas('logKeluarga', function ($q) {
            $q->select('log_keluarga.id')->selectRaw('Max(log_keluarga.id) as max')->where('log_keluarga.id_peristiwa', '!=', '2')->groupBy('log_keluarga.id');

            if (isset(request('filter')['tahun'])) {
                $q->whereYear('tgl_peristiwa', '<=', request('filter')['tahun']);
            }

            if (isset(request('filter')['bulan'])) {
                $q->whereMonth('tgl_peristiwa', '<=', request('filter')['bulan']);
            }
        });

        if (! isset(request('filter')['tahun']) && ! isset(request('filter')['bulan'])) {
            $query->status();
        }

        $query->get();

        return [
            'header' => $kelas,
            'footer' => $this->listFooter($kelas, $query),
        ];
    }
}
