<?php

namespace App\Models;

class Anak extends BaseModel
{
    /**
     * Static data status gizi anak.
     *
     * @var array
     */
    public const STATUS_GIZI_ANAK = [
        [
            'id' => 1,
            'simbol' => 'N',
            'nama' => 'Sehat / Normal (N)',
        ],
        [
            'id' => 2,
            'simbol' => 'GK',
            'nama' => 'Gizi Kurang (GK)',
        ],
        [
            'id' => 3,
            'simbol' => 'GB',
            'nama' => 'Gizi Buruk (GB)',
        ],
        [
            'id' => 4,
            'simbol' => 'S',
            'nama' => 'Stunting (S)',
        ],
    ];

    /**
     * Static data status tikar anak.
     *
     * @var array
     */
    public const STATUS_TIKAR_ANAK = [
        [
            'id' => 1,
            'simbol' => 'TD',
            'nama' => 'Tidak Diukur (TD)',
        ],
        [
            'id' => 2,
            'simbol' => 'M',
            'nama' => 'Merah (M)',
        ],
        [
            'id' => 3,
            'simbol' => 'K',
            'nama' => 'Kuning (K)',
        ],
        [
            'id' => 4,
            'simbol' => 'H',
            'nama' => 'Hijau (H)',
        ],
    ];

    /**
     * Static data status imunisasi campak.
     *
     * @var array
     */
    public const STATUS_IMUNISASI_CAMPAK = [
        1 => 'Belum',
        2 => 'Sudah',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bulanan_anak';

    /**
     * The table update parameter.
     *
     * @var string
     */
    public $primaryKey = 'id_bulanan_anak';

    /**
     * The guarded with the model.
     *
     * @var array
     */
    protected $guarded = [];

    public function kia()
    {
        return $this->belongsTo(KIA::class, 'kia_id');
    }

    public function scopeFilter($query, array $filters)
    {
        if (! empty($filters['bulan'])) {
            $query->whereMonth('bulanan_anak.created_at', $filters['bulan']);
        }

        if (! empty($filters['tahun'])) {
            $query->whereYear('bulanan_anak.created_at', $filters['tahun']);
        }

        if (! empty($filters['posyandu'])) {
            $query->where('posyandu_id', $filters['posyandu']);
        }

        return $query;
    }
}
