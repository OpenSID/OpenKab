<?php

namespace App\Http\Repository;

use App\Models\Umur;
use App\Models\Hamil;
use App\Models\Penduduk;
use Illuminate\Support\Facades\DB;
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
        return collect(match ($kategori) {
            'rentang-umur' => $this->caseRentangUmur(),
            'kategori-umur' => $this->caseKategoriUmur(),
            'akta-kelahiran' => $this->caseAktaKelahiran(),
            'status-kehamilan' => $this->caseStatusKehamilan(),
            'covid' => $this->caseCovid(),
            'suku' => $this->caseSuku(),
            'pendidikan-kk' => $this->casePendidikanKk(),
            'pendidikan-tempuh' => $this->casePendidikanTempuh(),
            'kerja' => $this->caseKerja(),
            'kawin' => $this->caseKawin(),
            'agama' => $this->caseAgama(),
            'jk' => $this->caseJk(),
            'wn' => $this->caseWn(),
            'status-penduduk' => $this->caseStatusPenduduk(),
            'darah' => $this->caseDarah(),
            'cacat' => $this->caseCacat(),
            'sakit' => $this->caseSakit(),
            'kb' => $this->caseKb(),
            'ktp' => $this->caseKtp(),
            'asuransi' => $this->caseAsuransi(),
            'bpjs_kerja' => $this->caseBpjsKerja(),
            'hubungan-kk' => $this->caseHubunganKk(),
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
        $data_header = collect($data_header);

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

    /**
     * Rentang Umur
     *
     * return array
     */
    private function caseRentangUmur()
    {
        $umur = Umur::countStatistikUmur()->status()->orderBy('id')->get();
        $query = $this->countStatistikPendudukHidup();

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur, $query),
        ];
    }

    /**
     * Kategori Umur
     *
     * return array
     */
    private function caseKategoriUmur()
    {
        $umur = Umur::countStatistikUmur()->status(0)->orderBy('id')->get();
        $query = $this->countStatistikPendudukHidup();

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur, $query),
        ];
    }

    /**
     * Akta Kelahiran
     *
     * return array
     */
    private function caseAktaKelahiran()
    {
        $umur = Umur::countStatistikAkta()->status()->orderBy('id')->get();
        $query = $this->countStatistikPendudukHidup();

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur, $query),
        ];
    }

    /**
     * Status Kehamilan
     *
     * return array
     */
    private function caseStatusKehamilan()
    {
        $where = 'tweb_penduduk.sex = 2';
        $hamil = $this->countStatistikByKategori('ref_penduduk_hamil', 'hamil', $where);
        $query = $this->countStatistikPendudukHidup();

        return [
            'header' => $hamil,
            'footer' => $this->listFooter($hamil, $query),
        ];
    }

    /**
     * Jumlah penduduk hidup
     *
     * return Collection
     */
    private function countStatistikPendudukHidup()
    {
        return Penduduk::countStatistik()->status()->get();
    }

    /**
     * Jumlah Laki-laki dan Perempuan berdasarkan kategori
     *
     * return array
     */
    public function countStatistikByKategori(string $tabelReferensi, string $idReferensi, string $where = null)
    {
        $query = DB::connection('openkab')
            ->table("{$tabelReferensi}")
            ->select("{$tabelReferensi}.id", "{$tabelReferensi}.nama");

        if (session()->has('desa')) {
            $query->where('config_id', session('desa.id'));
        }

        // if ($where) {
        //     $query->whereRaw($where);
        // }

        $result = $query->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('tweb_penduduk', "tweb_penduduk.{$idReferensi}", '=', "{$tabelReferensi}.id", 'left')
            ->where('tweb_penduduk.status', 1)
            ->groupBy("{$tabelReferensi}.id")
            ->get();

        return collect($result)->values()->toArray();
    }
}
