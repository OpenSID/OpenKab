<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'statistik',
        'nama_sasaran'
    ];

    /** {@inheritdoc} */
    protected $casts = [
        // 'sasaran' => SasaranEnum::class,
    ];

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
        return match($this->sasaran) {
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
                'jumlah'    => $peserta->jumlah,
                'laki_laki' => $peserta->laki_laki,
                'perempuan' => $peserta->perempuan,
                'persentase_jumlah' => $total->jumlah > 0 ? $peserta->jumlah / $total->jumlah * 100 : 0,
                'persentase_laki_laki' => $total->laki_laki > 0 ? $peserta->laki_laki / $total->laki_laki * 100 : 0,
                'persentase_perempuan' => $total->perempuan > 0 ? $peserta->perempuan / $total->perempuan * 100 : 0,
                'nama'      => 'Peserta',
            ],
            [
                'jumlah'    => $total->jumlah - $peserta->jumlah,
                'laki_laki' => $total->laki_laki - $peserta->laki_laki,
                'perempuan' => $total->perempuan - $peserta->perempuan,
                'persentase_jumlah' => $total->jumlah > 0 ? ($total->jumlah - $peserta->jumlah) / $total->jumlah * 100 : 0,
                'persentase_laki_laki' => $total->laki_laki > 0 ? ($total->laki_laki - $peserta->laki_laki) / $total->laki_laki * 100 : 0,
                'persentase_perempuan' => $total->perempuan > 0 ? ($total->perempuan - $peserta->perempuan) / $total->perempuan * 100 : 0,
                'nama'      => 'Bukan Peserta',
            ],
            [
                'jumlah'    => $total->jumlah,
                'laki_laki' => $total->laki_laki,
                'perempuan' => $total->perempuan,
                'persentase_jumlah' => 100,
                'persentase_laki_laki' => 100,
                'persentase_perempuan' => 100,
                'nama'      => 'Total',
            ],
        ];
    }

    private function getPeserta($id, $sasaran)
    {
        $query = DB::connection($this->connection)->table('program_peserta')
            ->selectRaw('COUNT(tweb_penduduk.id) AS jumlah')
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
                    ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'kelompok.id_ketua', 'left');

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
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->first();
    }
}
