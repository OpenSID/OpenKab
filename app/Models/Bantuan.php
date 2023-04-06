<?php

namespace App\Models;

use App\Models\Enums\SasaranEnum;
use Illuminate\Support\Facades\DB;
use App\Models\Traits\ConfigIdTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bantuan extends Model
{
    use ConfigIdTrait;

    public const KATEGORI_STATISTIK = [
        'bdt' => 'BDT',
    ];

    /** {@inheritdoc} */
    protected $connection = 'openkab';

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

    /** {@inheritdoc} */
    protected $dbConnection;

    /**
     * constract
     */
    public function __construct()
    {
        $this->dbConnection = DB::connection($this->connection);
    }

    /**
     * Define a one-to-many relationship.
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
        return $this->getStatistik($this->id, $this->sasaran);
    }

    private function getStatistik($id, $sasaran)
    {
        $peserta = $this->getPeserta($id, $sasaran);
        $total  = $this->getTotal($sasaran);

        return [
            [
                'laki_laki' => $peserta->laki_laki,
                'perempuan' => $peserta->perempuan,
            ],
            [
                'laki_laki' => $total->laki_laki,
                'perempuan' => $total->perempuan,
            ],
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

    private function getTotal($sasaran)
    {
        $query = null;
        switch ($sasaran) {
            case '1':
                $query = $this->dbConnection->table('tweb_penduduk');

                break;
            case '2':
                $query = $this->dbConnection->table('tweb_keluarga')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_keluarga.nik_kepala', 'left');

                break;
            case '3':
                $query = $this->dbConnection->table('tweb_rtm')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_rtm.nik_kepala', 'left');

                break;

            case '4':
                $query = $this->dbConnection->table('kelompok')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'kelompok.id_ketua', 'left')
                    ->where('kelompok.tipe', 'kelompok');

                break;
        }

        return $query->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->first();
    }
}
