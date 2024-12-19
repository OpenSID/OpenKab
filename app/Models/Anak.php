<?php

namespace App\Models;

class Anak extends BaseModel
{
    public const NORMAL = 1;

    public const GIZI_KURANG = 2;

    public const GIZI_BURUK = 3;

    public const STUNTING = 4;

    public const TB_SANGAT_PENDEK = 2;

    public const TB_PENDEK = 3;

    public const KATEGORI_STATISTIK = [
        'stunting' => 'Stunting',
    ];

    /**
     * Static data status gizi anak.
     *
     * @var array
     */
    public const STATUS_GIZI_ANAK = [
        [
            'id' => self::NORMAL,
            'simbol' => 'N',
            'nama' => 'Sehat / Normal (N)',
        ],
        [
            'id' => self::GIZI_KURANG,
            'simbol' => 'GK',
            'nama' => 'Gizi Kurang (GK)',
        ],
        [
            'id' => self::GIZI_BURUK,
            'simbol' => 'GB',
            'nama' => 'Gizi Buruk (GB)',
        ],
        [
            'id' => self::STUNTING,
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

    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class, 'posyandu_id');
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

    protected function scopeNormal($query)
    {
        return $query->where('status_gizi', self::NORMAL);
    }

    protected function scopeResikoStunting($query)
    {
        return $query->whereIn('status_gizi', [self::GIZI_BURUK, self::GIZI_KURANG]);
    }

    protected function scopeStunting($query)
    {
        return $query->where('status_gizi', self::STUNTING);
    }

    protected function scopeStuntingPendek($query)
    {
        return $query->stunting()->whereIn('status_tikar', [self::TB_PENDEK, self::TB_SANGAT_PENDEK]);
    }

    public function isNormal()
    {
        return $this->attributes['status_gizi'] == self::NORMAL;
    }

    public function isResikoStunting()
    {
        return in_array($this->attributes['status_gizi'], [self::GIZI_BURUK, self::GIZI_KURANG]);
    }

    public function isStunting()
    {
        return $this->attributes['status_gizi'] == self::STUNTING;
    }
}
