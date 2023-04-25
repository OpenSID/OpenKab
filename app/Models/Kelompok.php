<?php

namespace App\Models;

class Kelompok extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kelompok';

    /**
     * The timestamps for the model.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The guarded with the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Define a one-to-many relationship.
     *
     * @return HasMany
     */
    public function ketua()
    {
        return $this->hasOne(Penduduk::class, 'id', 'id_ketua');
    }

    /**
     * Scope query untuk status kelompok.
     *
     * @param mixed $query
     * @param mixed $status
     *
     * @return Builder
     */
    public function scopeStatus($query, $status = 1)
    {
        return $query->whereHas('ketua', static function ($q) use ($status) {
            $q->status($status);
        });
    }

    /**
     * Scope query untuk tipe kelompok.
     *
     * @param mixed $query
     * @param mixed $tipe
     *
     * @return Builder
     */
    public function scopeTipe($query, $tipe = 'kelompok')
    {
        return $query->where('tipe', $tipe);
    }

    /**
     * Scope untuk Statistik.
     */
    public function scopeCountStatistik($query)
    {
        $this->appends = [];
        $this->with = [];

        return $this->scopeConfigId($query)
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('tweb_penduduk', 'tweb_penduduk.id', '=', "{$this->table}.id_ketua", 'left')
            ->where('tweb_penduduk.status_dasar', 1);
    }
}
