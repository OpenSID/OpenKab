<?php

namespace App\Models;

use App\Models\Traits\FilterWilayahTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Bantuan extends BaseModel
{
    use FilterWilayahTrait;

    public const SASARAN_PENDUDUK = 1;

    public const SASARAN_KELUARGA = 2;

    public const SASARAN_RUMAH_TANGGA = 3;

    public const SASARAN_KELOMPOK = 4;

    public const KATEGORI_STATISTIK = [
        'penduduk' => 'Penerima Bantuan Penduduk',
        'keluarga' => 'Penerima Bantuan Keluarga',
        // 'rtm' => 'Penerima Bantuan Rumah Tangga',
        // 'kelompok' => 'Penerima Bantuan Kelompok',
    ];

    /** {@inheritdoc} */
    protected $table = 'program';

    /** {@inheritdoc} */
    protected $appends = [
        'statistik',
        'nama_sasaran',
        'jumlah_peserta',
        'nama_status',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        // 'sasaran' => SasaranEnum::class,
    ];

    /**
     * Define a one-to-many relationshitweb_penduduk.
     *
     * @return HasMany
     */
    public function peserta()
    {
        return $this->hasMany(BantuanPeserta::class, 'program_id');
    }

    public function getNamaSasaranAttribute()
    {
        return match ($this->sasaran) {
            1 => 'Penduduk',
            2 => 'Keluarga',
            3 => 'Rumah Tangga',
            4 => 'Kelompok/Organisasi Kemasyarakatan',
            default => null,
        };
    }

    public function getNamaStatusAttribute()
    {
        return match ($this->status) {
            0 => 'Tidak Aktif',
            1 => 'Aktif',
            default => 0,
        };
    }

    public function getJumlahPesertaAttribute()
    {
        return $this->peserta->count();
    }

    public function getStatistikAttribute()
    {
        $peserta = $this->getPeserta($this->id, $this->sasaran);

        return [
            'laki_laki' => $peserta->laki_laki ?? 0,
            'perempuan' => $peserta->perempuan ?? 0,
        ];
    }

    private function getPeserta($id, $sasaran)
    {
        $query = $this->dbConnection->table('program_peserta')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->where('program_peserta.program_id', $id);

        if (session()->has('desa')) {
            $query->where('program_peserta.config_id', session('desa.id'));
        }

        switch ($sasaran) {
            case Bantuan::SASARAN_PENDUDUK:
                $query->join('tweb_penduduk', 'tweb_penduduk.nik', '=', 'program_peserta.peserta', 'left');

                break;
            case Bantuan::SASARAN_KELUARGA:
                $query->join('tweb_keluarga', 'tweb_keluarga.no_kk', '=', 'program_peserta.peserta', 'left')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_keluarga.nik_kepala', 'left');

                break;
            case Bantuan::SASARAN_RUMAH_TANGGA:
                $query->join('tweb_rtm', 'tweb_rtm.no_kk', '=', 'program_peserta.peserta', 'left')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_rtm.nik_kepala', 'left');

                break;
            case Bantuan::SASARAN_KELOMPOK:
                $query->join('kelompok', 'kelompok.id', '=', 'program_peserta.peserta', 'left')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'kelompok.id_ketua', 'left')
                    ->where('kelompok.tipe', 'kelompok');

                break;
            default:
                return [];
        }

        return $query->where('tweb_penduduk.status_dasar', 1)->first();
    }

    /**
     * Scope untuk Statistik Sasaran Penduduk.
     */
    public function scopeCountStatistikPenduduk($query)
    {
        $configDesa = null;
        if (request('config_desa')) {
            $configDesa = request('config_desa');
        }

        if (isset(request('filter')['tahun']) || isset(request('filter')['bulan'])) {
            $periode = [request('filter')['tahun'] ?? date('Y'), request('filter')['bulan'] ?? '12', '01'];
            $tanggalPeristiwa = Carbon::parse(implode('-', $periode))->endOfMonth()->format('Y-m-d');
            $logPenduduk = LogPenduduk::select(['log_penduduk.id_pend'])->peristiwaTerakhir($tanggalPeristiwa, $configDesa)->tidakMati()->toBoundSql();
        }

        $statistik = $this->scopeConfigId($query)
            ->select(["{$this->table}.id", "{$this->table}.nama"])
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('program_peserta', 'program_peserta.program_id', '=', "{$this->table}.id", 'left')
            ->join('tweb_penduduk', 'program_peserta.peserta', '=', 'tweb_penduduk.nik', 'left')
            ->where('tweb_penduduk.status_dasar', 1)
            ->where('program.sasaran', self::SASARAN_PENDUDUK)
            ->groupBy("{$this->table}.id", "{$this->table}.nama");

        if (isset($logPenduduk)) {
            $statistik->join(DB::raw("($logPenduduk) as log"), 'log.id_pend', '=', 'tweb_penduduk.id');
        }

        if ($configDesa) {
            $statistik->where(function ($q) use ($configDesa) {
                return $q->where("{$this->table}.config_id", $configDesa)->orWhereNull("{$this->table}.config_id");
            });
        }

        return $statistik;
    }

    /**
     * Scope untuk Statistik Sasaran Keluarga.
     */
    public function scopeCountStatistikKeluarga($query)
    {
        $configDesa = null;
        if (request('config_desa')) {
            $configDesa = request('config_desa');
        }
        $query = $this->scopeConfigId($query)
            ->select(["{$this->table}.id", "{$this->table}.nama"])
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('program_peserta', 'program_peserta.program_id', '=', "{$this->table}.id", 'left')
            ->join('tweb_keluarga', 'program_peserta.peserta', '=', 'tweb_keluarga.no_kk', 'left')
            ->join('tweb_penduduk', 'tweb_keluarga.nik_kepala', '=', 'tweb_penduduk.id', 'left')
            ->where('tweb_penduduk.status_dasar', 1)
            ->where('program.sasaran', self::SASARAN_KELUARGA)
            ->groupBy("{$this->table}.id", "{$this->table}.nama");

        if ($configDesa) {
            $query->where(function ($q) use ($configDesa) {
                return $q->where("{$this->table}.config_id", $configDesa)->orWhereNull("{$this->table}.config_id");
            });
        }

        return $query;
    }

    /**
     * Scope untuk Sasaran.
     */
    public function scopeSasaran($query, $sasaran = self::SASARAN_PENDUDUK)
    {
        return $query->where('sasaran', $sasaran);
    }

    public function scopeTahun($query)
    {
        return $query->selectRaw('YEAR(MIN(sdate)) AS tahun_awal, YEAR(MAX(edate)) AS tahun_akhir');
    }
}
