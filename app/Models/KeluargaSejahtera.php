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
        $keluarga = $this->getKeluarga($id);
        $total  = $this->getTotal();

        return [
            [
                'jumlah'    => $keluarga->jumlah,
                'persentase_jumlah' => $total->jumlah > 0 ? $keluarga->jumlah / $total->jumlah * 100 : 0,
                'nama'      => 'Keluarga',
            ],
            [
                'jumlah'    => $total->jumlah - $keluarga->jumlah,
                'persentase_jumlah' => $total->jumlah > 0 ? ($total->jumlah - $keluarga->jumlah) / $total->jumlah * 100 : 0,
                'nama'      => 'Belum Mengisi',
            ],
            [
                'jumlah'    => $total->jumlah,
                'persentase_jumlah' => 100,
                'nama'      => 'Total',
            ],
        ];
    }

    private function getKeluarga($id)
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
