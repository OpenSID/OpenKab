<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Keluarga extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'tweb_keluarga';

    /** {@inheritdoc} */
    protected $appends = [
        'statistik',
    ];

    /**
     * {@inheritDoc}
     */
    protected $with = [
        'kelasSosial',
    ];

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function kelasSosial()
    {
        return $this->belongsTo(KeluargaSejahtera::class, 'kelas_sosial')->withDefault();
    }

    public function getStatistikAttribute()
    {
        return $this->getStatistik($this->id, $this->kelas_sosial);
    }

    private function getStatistik($id, $kelas_sosial)
    {
        $peserta = $this->getPeserta($id, $kelas_sosial);
        $total  = $this->getTotal($kelas_sosial);

        return [
            [
                'jumlah'    => $peserta->jumlah,
                'persentase_jumlah' => $total->jumlah > 0 ? $peserta->jumlah / $total->jumlah * 100 : 0,
                'nama'      => 'Jumlah',
            ],
            [
                'jumlah'    => $total->jumlah - $peserta->jumlah,
                'persentase_jumlah' => $total->jumlah > 0 ? ($total->jumlah - $peserta->jumlah) / $total->jumlah * 100 : 0,
                'nama'      => 'Belum Mengisi',
            ],
            [
                'jumlah'    => $total->jumlah,
                'persentase_jumlah' => 100,
                'nama'      => 'Total',
            ],
        ];
    }

    private function getPeserta($id)
    {
        $query = DB::connection($this->connection)->table('tweb_keluarga_sejahtera')
            ->selectRaw('COUNT(tweb_keluarga.id) AS jumlah')
            ->where('tweb_keluarga_sejahtera.id', $id)
            ->join('tweb_keluarga', 'tweb_keluarga.kelas_sosial', '=', 'tweb_keluarga_sejahtera.id', 'left');

        return $query->first();
    }

    private function getTotal()
    {
        $query = DB::connection($this->connection)->table('tweb_keluarga');

        return $query->selectRaw('COUNT(tweb_keluarga.id) AS jumlah')->first();
    }
}
