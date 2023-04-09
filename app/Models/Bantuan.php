<?php

namespace App\Models;

use App\Models\Enums\SasaranEnum;
use App\Models\Traits\ConfigIdTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bantuan extends BaseModel
{
    use ConfigIdTrait;

    public const SASARAN_PENDUDUK = 1;
    public const SASARAN_KELUARGA = 2;
    public const SASARAN_RUMAH_TANGGA = 3;
    public const SASARAN_KELOMPOK = 4;

    public const KATEGORI_STATISTIK = [
        'bantuan-penduduk' => 'Penerima Bantuan Penduduk',
        'bantuan-keluarga' => 'Penerima Bantuan Keluarga',
    ];

    /** {@inheritdoc} */
    protected $table = 'program';

    /** {@inheritdoc} */
    protected $appends = [
        'statistik',
        'nama_sasaran'
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

    public function getStatistikAttribute()
    {
        $peserta = $this->getPeserta($this->id, $this->sasaran);

        return [
            'laki_laki' => $peserta->laki_laki,
            'perempuan' => $peserta->perempuan,
        ];
    }

    private function getPeserta($id, $sasaran)
    {
        $query = $this->dbConnection->table('program_peserta')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->where('program_peserta.program_id', $id);

        switch ($sasaran) {
            case '1':
                $query->join('tweb_penduduk', 'tweb_penduduk.nik', '=', 'program_peserta.peserta', 'left');

                break;
            case '2':
                $query->join('tweb_keluarga', 'tweb_keluarga.no_kk', '=', 'program_peserta.peserta', 'left')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_keluarga.nik_kepala', 'left');

                break;
            case '3':
                $query->join('tweb_rtm', 'tweb_rtm.no_kk', '=', 'program_peserta.peserta', 'left')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_rtm.nik_kepala', 'left');

                break;
            case '4':
                $query->join('kelompok', 'kelompok.id', '=', 'program_peserta.peserta', 'left')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'kelompok.id_ketua', 'left')
                    ->where('kelompok.tipe', 'kelompok');

                break;
            default:
                return [];
        }

        return $query->first();
    }

    /**
     * Scope untuk Statistik Sasaran Penduduk
     */
    public function scopeCountStatistikPenduduk($query)
    {
        return $query->select(['program.id', 'program.nama'])
            ->select(['program.id', 'program.nama'])
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('program_peserta', 'program_peserta.program_id', '=', 'program.id', 'left')
            ->join('tweb_penduduk', 'program_peserta.peserta', '=', 'tweb_penduduk.nik', 'left')
            ->groupBy('program.id');
    }

    /**
     * Scope untuk Statistik Sasaran keluarga
     */
    public function scopeCountStatistikKeluarga($query)
    {
        // return $this->selectCountStatistikWithCase($query)
        return $query->select(['program.id', 'program.nama'])
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('program_peserta', 'program_peserta.program_id', '=', 'program.id', 'left')
            ->join('tweb_penduduk', 'program_peserta.peserta', '=', 'tweb_penduduk.nik', 'left')
            ->groupBy('program.id');
    }

    /**
     * Scope untuk Sasaran
     */
    public function scopeSasaran($query, $sasaran = self::SASARAN_PENDUDUK)
    {
        return $query->where('sasaran', $sasaran);
    }
}
