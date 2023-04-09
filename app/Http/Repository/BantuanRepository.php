<?php

namespace App\Http\Repository;

use App\Models\Bantuan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class BantuanRepository
{
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

    public function listStatistik($kategori): array|object
    {
        return collect(match ($kategori) {
            'penduduk' => $this->caseKategoriPenduduk($kategori, 1),
            'keluarga' => $this->caseKategoriKeluarga($kategori, 2),
            default => $this->caseNonKategori($kategori),
        })->toArray();
    }

    public function getBantuanNonKategori($id)
    {
        $query = Bantuan::query();

        if (session()->has('desa')) {
            $query->where('config_id', session('desa.id'));
        }

        $bantuan = $query->whereId($id)->first();

        return [
            [
                'nama' => 'PESERTA',
                'laki_laki' => $bantuan->statistik['laki_laki'],
                'perempuan' => $bantuan->statistik['perempuan'],
            ],
            $this->getTotal($bantuan->sasaran),
        ];
    }

    private function getTotal($sasaran)
    {
        $query = null;
        switch ($sasaran) {
            case Bantuan::SASARAN_PENDUDUK:
                $query = DB::connection('openkab')->table('tweb_penduduk');

                break;
            case Bantuan::SASARAN_KELUARGA:
                $query = DB::connection('openkab')->table('tweb_keluarga')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_keluarga.nik_kepala', 'left');

                break;
            case Bantuan::SASARAN_RUMAH_TANGGA:
                $query = DB::connection('openkab')->table('tweb_rtm')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_rtm.nik_kepala', 'left');

                break;

            case Bantuan::SASARAN_KELOMPOK:
                $query = DB::connection('openkab')->table('kelompok')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'kelompok.id_ketua', 'left')
                    ->where('kelompok.tipe', 'kelompok');

                break;
        }

        $result = $query->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->first();

        return [
            'laki_laki' => $result->laki_laki,
            'perempuan' => $result->perempuan,
        ];
    }

    public function caseKategoriBantuan($sasaran = null): array|object
    {
        $query = Bantuan::query();
        if (session()->has('desa')) {
            $query->where('program.config_id', session('desa.id'));
        }

        return $query->countStatistikPenduduk()->sasaran($sasaran)->get();
    }

    public function caseKategoriPenduduk($id, $sasaran = null): array
    {
        $header = $this->caseKategoriBantuan($sasaran);
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

        return $query->status()->get();
    }

    public function caseKategoriKeluarga($id, $sasaran = null): array
    {

        $header = $this->caseKategoriBantuan($sasaran);
        $footer = $this->countStatistikKategoriPenduduk();

        return [
            'header' => $header,
            'footer' => $this->listFooter($header, $footer),
        ];
    }

    private function countStatistikKategoriKeluarga(string $whereHeader = null): object
    {
        $query = Keluarga::countStatistik();

        if ($whereHeader) {
            $query->whereRaw($whereHeader);
        }

        return $query->status()->get();
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
