<?php

namespace App\Http\Repository;

use App\Models\Covid;
use App\Models\Ktp;
use App\Models\LogPenduduk;
use App\Models\Penduduk;
use App\Models\Umur;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PendudukRepository
{
    public function pendudukReferensi(string $class)
    {
        return QueryBuilder::for($class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('nama', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts(['id', 'nama'])
            ->jsonPaginate();
    }

    public function listPenduduk()
    {
        return QueryBuilder::for(Penduduk::withRef()->filterWilayah())
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('sex'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('status_dasar'),
                AllowedFilter::exact('keluarga.no_kk'),
                AllowedFilter::exact('clusterDesa.dusun'),
                AllowedFilter::exact('clusterDesa.rw'),
                AllowedFilter::exact('clusterDesa.rt'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('nama', 'like', "%{$value}%")
                            ->orWhere('nik', 'like', "%{$value}%")
                            ->orWhere('tag_id_card', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts([
                'nik',
                'nama',
                'umur',
                'created_at',
            ])
            ->jsonPaginate();
    }

    public function listPendudukKesehatan()
    {
        return QueryBuilder::for(Penduduk::select([
            'id',
            'nik',
            'nama',
            'golongan_darah_id',
            'cacat_id',
            'sakit_menahun_id',
            'cara_kb_id',
            'hamil',
            'id_asuransi',
            'no_asuransi',
        ]))
        ->allowedFields('*')  // Tentukan field yang diizinkan untuk dipilih
        ->allowedFilters([  // Tentukan filter yang diizinkan
            AllowedFilter::exact('id'),
            AllowedFilter::exact('sex'),
            AllowedFilter::exact('status'),
            AllowedFilter::exact('status_dasar'),
            AllowedFilter::exact('keluarga.no_kk'),
            AllowedFilter::exact('clusterDesa.dusun'),
            AllowedFilter::exact('clusterDesa.rw'),
            AllowedFilter::exact('clusterDesa.rt'),
            AllowedFilter::callback('search', function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('nama', 'like', "%{$value}%")
                        ->orWhere('nik', 'like', "%{$value}%")
                        ->orWhere('tag_id_card', 'like', "%{$value}%");
                });
            }),
        ])
        ->allowedSorts([  // Tentukan kolom yang dapat digunakan untuk sorting
            'nik',
            'nama',
            'umur',
            'created_at',
        ])
        ->jsonPaginate();
    }

    public function listPendudukJaminanSosial()
    {
        return QueryBuilder::for(Penduduk::withRef()->filterWilayah()->select([
            'id',
            'nik',
            'nama',
            'id_asuransi',
            'no_asuransi',
            'bpjs_ketenagakerjaan',
            'cacat_id',
        ]))
        ->allowedFields('*')  // Tentukan field yang diizinkan untuk dipilih
        ->allowedFilters([  // Tentukan filter yang diizinkan
            AllowedFilter::exact('id'),
            AllowedFilter::callback('search', function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->where('nama', 'like', "%{$value}%")
                        ->orWhere('nik', 'like', "%{$value}%");
                });
            }),
        ])
        ->allowedSorts([  // Tentukan kolom yang dapat digunakan untuk sorting
            'nik',
            'nama',
        ])
        ->jsonPaginate();
    }

    public function listPendudukProdeskel()
    {
        return QueryBuilder::for(Penduduk::with('prodeskelLembagaAdat')->withRef()->filterWilayah()->select([
                'id',
                'nik',
                'nama',
                'agama_id', 
                'suku',
                'config_id', // Pastikan config_id termasuk dalam query
            ]))
            ->allowedFields([
                'id', 'nik', 'nama', 'agama_id', 'suku', 'prodeskelLembagaAdat.id', 'prodeskelLembagaAdat.kategori', 'prodeskelLembagaAdat.data'
            ])
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('nama', 'like', "%{$value}%")
                            ->orWhere('nik', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts(['nik', 'nama'])
            ->jsonPaginate();  
    }

    
    public function listStatistik($kategori, $kabupaten, $kecamatan, $desa): array|object
    {
        return collect(match ($kategori) {
            'rentang-umur' => $this->caseRentangUmur($kabupaten, $kecamatan, $desa),
            'kategori-umur' => $this->caseKategoriUmur($kabupaten, $kecamatan, $desa),
            'akta-kelahiran' => $this->caseAktaKelahiran($kabupaten, $kecamatan, $desa),
            'status-covid' => $this->caseStatusCovid($kabupaten, $kecamatan, $desa),
            'suku' => $this->caseSuku($kabupaten, $kecamatan, $desa),
            'ktp' => $this->caseKtp($kabupaten, $kecamatan, $desa),
            default => $this->caseWithReferensi($kategori, $kabupaten, $kecamatan, $desa),
        })->toArray();
    }

    public function listTahun()
    {
        return LogPenduduk::tahun()->first();
    }

    private function tabelReferensi($kategori): array|object
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
            'akseptor-kb' => [
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
            'status-perkawinan' => [
                'idReferensi' => 'status_kawin',
                'tabelReferensi' => 'tweb_penduduk_kawin',
                'whereHeader' => null,
                'whereFooter' => null,
            ],
            'asuransi-kesehatan' => [
                'idReferensi' => 'id_asuransi',
                'tabelReferensi' => 'tweb_penduduk_asuransi',
                'whereHeader' => null,
                'whereFooter' => null,
            ],
            default => null,
        };
    }

    /**
     * @param $data_header  collection
     * @param $query_footer collection
     *
     * return array
     */
    private function listFooter($data_header, $query_footer): array|object
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

    private function caseRentangUmur($kabupaten, $kecamatan, $desa): array|object
    {
        $umur = Umur::countStatistikUmur()->status(Umur::RENTANG)->get();
        $query = $this->countStatistikPendudukHidup();

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur, $query),
        ];
    }

    private function caseKategoriUmur($kabupaten, $kecamatan, $desa): array|object
    {
        $umur = (new Umur())->setKlasifikasi(Umur::KATEGORI)->countStatistikUmur()->status(Umur::KATEGORI)->get();
        $query = $this->countStatistikPendudukHidup();

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur, $query),
        ];
    }

    private function caseAktaKelahiran($kabupaten, $kecamatan, $desa): array|object
    {
        $umur = Umur::countStatistikAkta()->status(Umur::RENTANG)->get();
        $query = $this->countStatistikPendudukHidup();

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur, $query),
        ];
    }

    private function caseWithReferensi(string $kategori, $kabupaten, $kecamatan, $desa): array|object
    {
        $referensi = $this->tabelReferensi($kategori);
        $header = $this->countStatistikByKategori($referensi['tabelReferensi'], $referensi['idReferensi'], $referensi['whereHeader']);
        $query = $this->countStatistikPendudukHidup($referensi['whereFooter']);

        return [
            'header' => $header,
            'footer' => $this->listFooter($header, $query),
        ];
    }

    private function countStatistikPendudukHidup(string $whereHeader = null): array|object
    {
        $tanggalPeristiwa = null;
        $configDesa = null;
        if (request('config_desa')) {
            $configDesa = request('config_desa');
        }
        if (isset(request('filter')['tahun']) || isset(request('filter')['bulan'])) {
            $periode = [request('filter')['tahun'] ?? date('Y'), request('filter')['bulan'] ?? '12', '01'];
            $tanggalPeristiwa = Carbon::parse(implode('-', $periode))->endOfMonth()->format('Y-m-d');
        }
        $logPenduduk = LogPenduduk::select(['log_penduduk.id_pend'])->peristiwaTerakhir($tanggalPeristiwa, $configDesa)->tidakMati()->toBoundSql();
        $penduduk = Penduduk::countStatistik()->join(DB::raw("($logPenduduk) as log"), 'log.id_pend', '=', 'tweb_penduduk.id');
        if (! isset(request('filter')['tahun']) && ! isset(request('filter')['bulan'])) {
            $penduduk->status();
        }

        if ($configDesa) {
            $penduduk->filterDesa();
        }

        return $penduduk->get();
    }

    public function countStatistikByKategori(string $tabelReferensi, string $idReferensi, string $whereFooter = null): array|object
    {
        $query = DB::connection('openkab')
            ->table("{$tabelReferensi}")
            ->select("{$tabelReferensi}.id", "{$tabelReferensi}.nama");

        if (session()->has('desa')) {
            $query->where('tweb_penduduk.config_id', session('desa.id'));
        }

        if (request('config_desa')) {
            $query->where('tweb_penduduk.config_id', request('config_desa'));
        }

        if ($whereFooter) {
            $query->whereRaw($whereFooter);
        }

        $tanggalPeristiwa = null;
        if (isset(request('filter')['tahun']) || isset(request('filter')['bulan'])) {
            $periode = [request('filter')['tahun'] ?? date('Y'), request('filter')['bulan'] ?? '12', '01'];
            $tanggalPeristiwa = Carbon::parse(implode('-', $periode))->endOfMonth()->format('Y-m-d');
        }
        $logPenduduk = LogPenduduk::select(['log_penduduk.id_pend'])->peristiwaTerakhir($tanggalPeristiwa)->tidakMati()->toBoundSql();

        $sql = $query->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('tweb_penduduk', "tweb_penduduk.{$idReferensi}", '=', "{$tabelReferensi}.id", 'left')
            ->join('config', 'config.id', '=', 'tweb_penduduk.config_id', 'left')
            ->where('tweb_penduduk.status_dasar', 1)
            ->join(DB::raw("($logPenduduk) as log"), 'log.id_pend', '=', 'tweb_penduduk.id')
            ->groupBy("{$tabelReferensi}.id", "{$tabelReferensi}.nama");

        return $sql->get();
    }

    private function caseSuku($kabupaten, $kecamatan, $desa): array|object
    {
        $umur = Penduduk::CountStatistikSuku()->orderBy('id')->get();
        $query = $this->countStatistikPendudukHidup();

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur, $query),
        ];
    }

    private function caseKtp($kabupaten, $kecamatan, $desa)
    {
        $whereFooter = "((DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW()) - TO_DAYS(tweb_penduduk.tanggallahir)), '%Y')+0)>=17 OR (tweb_penduduk.status_kawin IS NOT NULL AND tweb_penduduk.status_kawin <> 1))";
        $umur = Ktp::countStatistik()->orderBy('id')->get();
        $query = $this->countStatistikPendudukHidup($whereFooter);

        return [
            'header' => $umur,
            'footer' => $this->listFooter($umur, $query),
        ];
    }

    private function caseStatusCovid($kabupaten, $kecamatan, $desa): array|object
    {
        $covid = Covid::countStatistik()->orderBy('id')->get();
        $query = $this->countStatistikPendudukHidup();

        return [
            'header' => $covid,
            'footer' => $this->listFooter($covid, $query),
        ];
    }

    public function summary()
    {
        return QueryBuilder::for(Penduduk::class)->count();
    }

    public function listPendudukPendidikan()
    {
        return QueryBuilder::for(Penduduk::class)
            // ->select([
            //     'p.id',
            //     'p.nik',
            //     'p.pendidikan_kk_id',
            //     'p.pendidikan_sedang_id',
            //     'da.kd_partisipasi_sekolah',
            //     'da.kd_pendidikan_tertinggi',
            //     'da.kd_kelas_tertinggi',
            //     'da.kd_ijazah_tertinggi',
            // ])
            // ->from('tweb_penduduk as p')
            // ->leftJoin('config as c', 'p.config_id', '=', 'c.id')
            // ->leftJoin('dtks as d', 'd.config_id', '=', 'c.id')
            // ->leftJoin('dtks_anggota as da', 'da.id_dtks', '=', 'd.id')
            ->allowedFields('*')  // Tentukan field yang diizinkan untuk dipilih
            ->allowedFilters([  // Tentukan filter yang diizinkan
                AllowedFilter::exact('id'),
                AllowedFilter::exact('sex'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('status_dasar'),
                AllowedFilter::exact('keluarga.no_kk'),
                AllowedFilter::exact('clusterDesa.dusun'),
                AllowedFilter::exact('clusterDesa.rw'),
                AllowedFilter::exact('clusterDesa.rt'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('p.nama', 'like', "%{$value}%")
                            ->orWhere('p.nik', 'like', "%{$value}%")
                            ->orWhere('p.tag_id_card', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts([  // Tentukan kolom yang dapat digunakan untuk sorting
                'p.nik',
                'p.nama',
                'p.umur',
                'p.created_at',
            ])
            ->jsonPaginate();  // Melakukan pagination dan mengembalikan data dalam
    }

    public function listPendudukKetenagakerjaan()
    {
        return QueryBuilder::for(Penduduk::class)
            ->allowedFields('*')  // Tentukan field yang diizinkan untuk dipilih
            ->allowedFilters([  // Tentukan filter yang diizinkan
                AllowedFilter::exact('id'),
                AllowedFilter::exact('sex'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('status_dasar'),
                AllowedFilter::exact('keluarga.no_kk'),
                AllowedFilter::exact('clusterDesa.dusun'),
                AllowedFilter::exact('clusterDesa.rw'),
                AllowedFilter::exact('clusterDesa.rt'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('nama', 'like', "%{$value}%")
                            ->orWhere('nik', 'like', "%{$value}%")
                            ->orWhere('tag_id_card', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts([  // Tentukan kolom yang dapat digunakan untuk sorting
                'nik',
                'nama',
                'umur',
                'created_at',
            ])
            ->jsonPaginate();
    }
}
