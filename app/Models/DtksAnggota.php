<?php

namespace App\Models;

class DtksAnggota extends BaseModel
{
    /** {@inheritdoc} */
    
    protected $table = 'dtks_anggota';
    
    public function penduduk()
    {
        return $this->belongsTo(Penduduk::class, 'id');  // Asumsi anak_id di Kia mengarah ke Penduduk
    }

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dtks_anggota';

    /**
     * The timestamps for the model.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'config_id',
    ];

    public function config()
    {
        return $this->belongsTo(Config::class, 'config_id');
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function partisipasiSekolah()
    {
        return $this->belongsTo(Pendidikan::class, 'kd_partisipasi_sekolah')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function pendidikanTertinggi()
    {
        return $this->belongsTo(PendidikanKK::class, 'kd_pendidikan_tertinggi')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function kelasTertinggi()
    {
        return $this->belongsTo(Pendidikan::class, 'kd_kelas_tertinggi')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function ijazahTertinggi()
    {
        return $this->belongsTo(PendidikanKK::class, 'kd_ijazah_tertinggi')->withDefault();
    }
}
