<?php

namespace App\Http\Repository;

use App\Models\Umur;
use App\Models\Hamil;
use App\Models\Penduduk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            'covid' => $this->caseCovid(),
            'suku' => $this->caseSuku(),
            'ktp' => $this->caseKtp(),
            // Yang menggunakan tabel referensi
            default => $this->caseWithReferensi($kategori),
        })->toArray();
    }

    private function tabelReferensi($kategori)
    {
        return match ($kategori) {
            'status-kehamilan' => [
                'tabelReferensi' => 'ref_penduduk_hamil',
                'idReferensi' => 'hamil',
                'whereHeader' => 'tweb_penduduk.sex = 2',
                'whereFooter' => 'tweb_penduduk.sex = 2',
            ],
            'pendidikan-dalam-kk' => [
                'tabelReferensi' => 'tweb_penduduk_pendidikan_kk',
                'idReferensi' => 'pendidikan_kk_id',
                'whereHeader' => null,
                'whereFooter' => null,
            ],
            'pendidikan-sedang-ditempuh' => [
                'tabelReferensi' => 'tweb_penduduk_pendidikan',
                'idReferensi' => 'pendidikan_sedang_id',
                'whereHeader' => null,
                'whereFooter' => null,
            ],
            'bpjs-ketenagakerjaan' => [
                'tabelReferensi' => 'tweb_penduduk_pekerjaan',
                'idReferensi' => 'pekerjaan_id',
                'whereHeader' => '(bpjs_ketenagakerjaan IS NOT NULL && bpjs_ketenagakerjaan != "")',
                'whereFooter' => null,
            ],
            'jenis-kelamin' => [
                'idReferensi' => 'sex',
                'tabelReferensi' => 'tweb_penduduk_sex',
                'whereHeader' => null,
                'whereFooter' => null,
            ],
            'agama' => [
                'tabelReferensi' => 'tweb_penduduk_agama',
                'idReferensi' => 'agama_id',
                'whereHeader' => null,
                'whereFooter' => null,
            ],
            'warga-negara' => [
                'idReferensi' => 'warganegara_id',
                'tabelReferensi' => 'tweb_penduduk_warganegara',
                'whereHeader' => null,
                'whereFooter' => null,
            ],
            'pekerjaan' => [
                'tabelReferensi' => 'tweb_penduduk_pekerjaan',
                'idReferensi' => 'pekerjaan_id',
                'whereHeader' => null,
                'whereFooter' => null,
            ],
            'hubungan-dalam-kk' => [
                'tabelReferensi' => 'tweb_penduduk_hubungan',
                'idReferensi' => 'kk_level',
                'whereHeader' => null,
                'whereFooter' => null,
            ],
            'golongan-darah' => [
                'tabelReferensi' => 'tweb_golongan_darah',
                'idReferensi' => 'golongan_darah_id',
                'whereHeader' => null,
                'whereFooter' => null,
            ],
            'status-penduduk' => [
                'idReferensi' => 'status',
                'tabelReferensi' => 'tweb_penduduk_status',
                'whereHeader' => null,
                'whereFooter' => null,
            ],
            'aseptor-kb' => [
                'idReferensi' => 'cara_kb_id',
                'tabelReferensi' => 'tweb_cara_kb',
                'whereHeader' => null,
                'whereFooter' => null,
            ],
            'penyakit-menahun' => [
                'idReferensi' => 'sakit_menahun_id',
                'tabelReferensi' => 'tweb_sakit_menahun',
                'whereHeader' => null,
                'whereFooter' => null,
            ],
            'penyandang-cacat' => [
                'idReferensi' => 'cacat_id',
                'tabelReferensi' => 'tweb_cacat',
                'whereHeader' => null,
                'whereFooter' => null,
            ],
            // '2'           => ['idReferensi' => 'status_kawin', 'tabelReferensi' => 'tweb_penduduk_kawin'],
            // '4'           => ['idReferensi' => 'sex', 'tabelReferensi' => 'tweb_penduduk_sex'],
            // '6'           => ['idReferensi' => 'status', 'tabelReferensi' => 'tweb_penduduk_status'],
            // '9'           => ['idReferensi' => 'cacat_id', 'tabelReferensi' => 'tweb_cacat'],
            // '19'          => ['idReferensi' => 'id_asuransi', 'tabelReferensi' => 'tweb_penduduk_asuransi'],
            default => null,
        };
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
     * Menggunakan tabel referensi
     *
     * return array
     */
    private function caseWithReferensi(string $kategori)
    {
        $referensi = $this->tabelReferensi($kategori);
        $header = $this->countStatistikByKategori($referensi['tabelReferensi'], $referensi['idReferensi'], $referensi['whereHeader']);
        $query = $this->countStatistikPendudukHidup($referensi['whereFooter']);

        return [
            'header' => $header,
            'footer' => $this->listFooter($header, $query),
        ];
    }

    /**
     * Jumlah penduduk hidup
     *
     * return Collection
     */
    private function countStatistikPendudukHidup(string $whereHeader = null)
    {
        $query = Penduduk::countStatistik();

        if ($whereHeader) {
            $query->whereRaw($whereHeader);
        }

        return $query->status()->get();
    }

    /**
     * Jumlah Laki-laki dan Perempuan berdasarkan kategori
     *
     * return Collection
     */
    public function countStatistikByKategori(string $tabelReferensi, string $idReferensi, string $whereFooter = null)
    {
        $query = DB::connection('openkab')
            ->table("{$tabelReferensi}")
            ->select("{$tabelReferensi}.id", "{$tabelReferensi}.nama");

        if (session()->has('desa')) {
            $query->where('tweb_penduduk.config_id', session('desa.id'));
        }

        if ($whereFooter) {
            $query->whereRaw($whereFooter);
        }

        return $query->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('tweb_penduduk', "tweb_penduduk.{$idReferensi}", '=', "{$tabelReferensi}.id", 'left')
            ->where('tweb_penduduk.status_dasar', 1)
            ->groupBy("{$tabelReferensi}.id")
            ->get();
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
