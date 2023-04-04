<?php

namespace App\Models;

use App\Models\Enums\SasaranEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Bantuan extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'program';

    /** {@inheritdoc} */
    protected $appends = [
        'statistik'
    ];

    /** {@inheritdoc} */
    protected $casts = [
        // 'sasaran' => SasaranEnum::class,
    ];

    /**
     * Define a one-to-many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function peserta()
    {
        return $this->hasMany(BantuanPeserta::class, 'program_id');
    }

    public function getStatistikAttribute($query)
    {
        return $this->getStatistik($this->id, $this->sasaran);
    }

    private function getStatistik($id, $sasaran)
    {
        $peserta = $this->getPeserta($id, $sasaran);
        $total  = $this->getTotal($id, $sasaran);

        return [
            'peserta' => $peserta,
            'bukan_peserta' => [
                'total' => $total->jumlah - $peserta->jumlah,
                'laki' => $total->laki - $peserta->laki,
                'perempuan' => $total->perempuan - $peserta->perempuan,
            ],
            'total' => $total,
        ];
    }

    private function getPeserta($id, $sasaran)
    {
        $query = DB::connection($this->connection)->table('program_peserta')
            ->selectRaw('COUNT(tweb_penduduk.id) AS jumlah')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki')
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
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'kelompok.id_ketua', 'left');

                break;
            default:
                return [];
        }

        return $query->first();
    }

    private function getTotal($id, $sasaran)
    {
        switch ($sasaran) {
            case '1':
                $query = DB::connection($this->connection)->table('tweb_penduduk');

                break;
            case '2':
                $query = DB::connection($this->connection)->table('tweb_keluarga')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_keluarga.nik_kepala', 'left');

                break;
            case '3':
                $query = DB::connection($this->connection)->table('tweb_rtm')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_rtm.nik_kepala', 'left');

                break;

            case '4':
                $query = DB::connection($this->connection)->table('kelompok')
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'kelompok.id_ketua', 'left');

                break;
        }

        return $query->selectRaw('COUNT(tweb_penduduk.id) AS jumlah')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->first();
    }
}
