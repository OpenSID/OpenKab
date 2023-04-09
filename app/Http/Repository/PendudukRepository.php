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
        return collect(match ($kategori) {
            'rentang-umur' => $this->caseRentangUmur(),
            'kategori-umur' => $this->caseKategoriUmur(),
            'akta-kelahiran' => $this->caseAktaKelahiran(),
            'hamil' => $this->caseHamil(),
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

    // Hamil
    private function caseHamil()
    {
        $umur = Hamil::countStatistik()->where('nama', 'Hamil')->orderBy('id')->get();
        $query = Penduduk::countStatistik()->status()->get();

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur),
        ];
    }

    /**
     * Jumlah penduduk hidup
     *
     * return int
     */
    private function countStatistikPendudukHidup()
    {
        return Penduduk::countStatistik()->status()->get();
    }

    /**
     * Suku / Etnis
     *
     * return array
     */
    private function caseSuku()
    {
        $umur = Penduduk::CountStatistikSuku()->orderBy('id')->get();
        $query = $this->countStatistikPendudukHidup();

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur, $query),
        ];
    }
}
