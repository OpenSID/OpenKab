<?php

namespace App\Models;

use App\Models\Traits\FilterWilayahTrait;

class DtksAnggota extends BaseModel
{
    use FilterWilayahTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dtks_anggota';

    /**
     * The guarded with the model.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'tgl_lahir' => 'date:Y-m-d',
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
    ];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['dtks'];

    /**
     * Define a one-to-one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function dtks()
    {
        return $this->belongsTo(DTKS::class, 'id_dtks', 'id')->withoutGlobalScope(\App\Scopes\ConfigIdScope::class);
    }

    public function penduduk()
    {
        return $this->belongsTo(Penduduk::class, 'id_penduduk', 'id')->withoutGlobalScope(\App\Scopes\ConfigIdScope::class);
    }

    public function getUmurAttribute()
    {
        return $this->penduduk->umur;
    }

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
