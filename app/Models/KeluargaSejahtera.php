<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class KeluargaSejahtera extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tweb_keluarga_sejahtera';

    /** {@inheritdoc} */
    protected $appends = [
        'statistik',
    ];

    /**
     * Define a one-to-many relationship.
     *
     * @return HasMany
     */
    public function keluarga()
    {
        return $this->hasMany(Keluarga::class, 'id');
    }

    public function getStatistikAttribute()
    {
        return $this->getStatistik($this->id, $this->kelas_sosial);
    }

    private function getStatistik($id)
    {
        $keluarga       = $this->getKeluarga($id);
        $total_keluarga = $this->getTotalKeluarga();
        $total          = $this->getTotal();

        return [
            [
                'jumlah'    => $keluarga->jumlah,
                'laki_laki' => $keluarga->laki_laki,
                'perempuan' => $keluarga->perempuan,
                'persentase_jumlah' => $total->jumlah > 0 ? $keluarga->jumlah / $total->jumlah * 100 : 0,
                'persentase_laki_laki' => $total->laki_laki > 0 ? $keluarga->laki_laki / $total->laki_laki * 100 : 0,
                'persentase_perempuan' => $total->perempuan > 0 ? $keluarga->perempuan / $total->perempuan * 100 : 0,
                'nama'      => 'Keluarga',
            ],
            [
                'jumlah'    => $total_keluarga->jumlah,
                'laki_laki' => $total_keluarga->laki_laki,
                'perempuan' => $total_keluarga->perempuan,
                'persentase_jumlah' => $total->jumlah > 0 ? $total_keluarga->jumlah / $total->jumlah * 100 : 0,
                'persentase_laki_laki' => $total->laki_laki > 0 ? $total_keluarga->laki_laki / $total->laki_laki * 100 : 0,
                'persentase_perempuan' => $total->perempuan > 0 ? $total_keluarga->perempuan / $total->perempuan * 100 : 0,
                'nama'      => 'Jumlah',
            ],
            [
                'jumlah'    => $total->jumlah - $total_keluarga->jumlah,
                'laki_laki' => $total->laki_laki - $total_keluarga->laki_laki,
                'perempuan' => $total->perempuan - $total_keluarga->perempuan,
                'persentase_jumlah' => $total->jumlah > 0 ? ($total->jumlah - $total_keluarga->jumlah) / $total->jumlah * 100 : 0,
                'persentase_laki_laki' => $total->laki_laki > 0 ? ($total->laki_laki - $total_keluarga->laki_laki) / $total->laki_laki * 100 : 0,
                'persentase_perempuan' => $total->perempuan > 0 ? ($total->perempuan - $total_keluarga->perempuan) / $total->perempuan * 100 : 0,
                'nama'      => 'Belum Mengisi',
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

    private function getKeluarga($id)
    {
        return DB::connection($this->connection)->table('tweb_keluarga_sejahtera')
            ->selectRaw('COUNT(tweb_keluarga.id) AS jumlah')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->where('tweb_keluarga_sejahtera.id', $id)
            ->join('tweb_keluarga', 'tweb_keluarga.kelas_sosial', '=', 'tweb_keluarga_sejahtera.id', 'left')
            ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_keluarga.nik_kepala', 'left')
            ->first();
    }

    private function getTotal()
    {
        return DB::connection($this->connection)->table('tweb_keluarga')
            ->selectRaw('COUNT(tweb_keluarga.id) AS jumlah')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_keluarga.nik_kepala', 'left')
            ->first();
    }

    private function getTotalKeluarga()
    {
        return DB::connection($this->connection)->table('tweb_keluarga')
            ->selectRaw('COUNT(tweb_keluarga.id) AS jumlah')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('tweb_penduduk', 'tweb_penduduk.id', '=', 'tweb_keluarga.nik_kepala', 'left')
            ->where('kelas_sosial', '!=', null)
            ->first();
    }
}
