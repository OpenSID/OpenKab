<?php

namespace App\Models;

use App\Models\Traits\ConfigIdTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property \App\Models\Enums\TempatDilahirkanEnum $tempat_dilahirkan
 * @property \App\Models\Enums\JenisKelahiranEnum $jenis_kelahiran
 * @property \App\Models\Enums\PenolongKelahiranEnum $penolong_kelahiran
 */
class Penduduk extends Model
{
    use ConfigIdTrait;
    use HasFactory;

    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'tweb_penduduk';

    /** {@inheritdoc} */
    protected $fillable = [
        'email',
    ];

    /** {@inheritdoc} */
    protected $with = [
        'jenisKelamin',
        'agama',
        'pendidikan',
        'pendidikanKK',
        'pekerjaan',
        'wargaNegara',
        'golonganDarah',
        'cacat',
        'sakitMenahun',
        'kb',
        'statusKawin',
        'pendudukHubungan',
        'pendudukStatus',
        'keluarga',
        'rtm',
        'clusterDesa',
        'logPenduduk',
        'logPerubahanPenduduk',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'tanggallahir' => 'datetime',
        'tempat_dilahirkan' => \App\Models\Enums\TempatDilahirkanEnum::class,
        'jenis_kelahiran' => \App\Models\Enums\JenisKelahiranEnum::class,
        'penolong_kelahiran' => \App\Models\Enums\PenolongKelahiranEnum::class,
    ];

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function jenisKelamin()
    {
        return $this->belongsTo(Sex::class, 'sex')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function agama()
    {
        return $this->belongsTo(Agama::class, 'agama_id')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function pendidikan()
    {
        return $this->belongsTo(Pendidikan::class, 'pendidikan_sedang_id')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function pendidikanKK()
    {
        return $this->belongsTo(PendidikanKK::class, 'pendidikan_kk_id')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function pekerjaan()
    {
        return $this->belongsTo(Pekerjaan::class, 'pekerjaan_id')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function wargaNegara()
    {
        return $this->belongsTo(WargaNegara::class, 'warganegara_id')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function golonganDarah()
    {
        return $this->belongsTo(GolonganDarah::class, 'golongan_darah_id')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function cacat()
    {
        return $this->belongsTo(Cacat::class, 'cacat_id')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function sakitMenahun()
    {
        return $this->belongsTo(SakitMenahun::class, 'sakit_menahun_id')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function kb()
    {
        return $this->belongsTo(KB::class, 'cara_kb_id')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function statusKawin()
    {
        return $this->belongsTo(StatusKawin::class, 'status_kawin')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function pendudukHubungan()
    {
        return $this->belongsTo(PendudukHubungan::class, 'kk_level')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function pendudukStatus()
    {
        return $this->belongsTo(PendudukStatus::class, 'status')->withDefault();
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class, 'id_kk')->withDefault();
    }

    public function rtm()
    {
        return $this->belongsTo(Rtm::class, 'id_rtm', 'no_kk');
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function clusterDesa()
    {
        return $this->belongsTo(ClusterDesa::class, 'id_cluster')->withDefault();
    }

    public function logPenduduk()
    {
        return $this->hasMany(LogPenduduk::class, 'id_pend');
    }

    public function logPerubahanPenduduk()
    {
        return $this->hasMany(LogPerubahanPenduduk::class, 'id_pend');
    }

    /**
     * Getter wajib ktp attribute.
     *
     * @return string
     */
    public function getWajibKTPAttribute()
    {
        return (($this->tanggallahir->age > 16) || (! empty($this->status_kawin) && $this->status_kawin != 1))
            ? 'WAJIB KTP'
            : 'BELUM';
    }

    /**
     * Getter status perkawinan attribute.
     *
     * @return string
     */
    public function getStatusPerkawinanAttribute()
    {
        return ! empty($this->status_kawin) && $this->status_kawin != 2
            ? $this->statusKawin->nama
            : (
                empty($this->akta_perkawinan)
                    ? 'KAWIN BELUM TERCATAT'
                    : 'KAWIN TERCATAT'
            );
    }

    /**
     * Getter status hamil attribute.
     *
     * @return string
     */
    public function getStatusHamilAttribute()
    {
        return empty($this->hamil) ? 'TIDAK HAMIL' : 'HAMIL';
    }

    /**
     * Getter nama asuransi attribute.
     *
     * @return string
     */
    public function getNamaAsuransiAttribute()
    {
        return ! empty($this->id_asuransi) && $this->id_asuransi != 1
            ? (($this->id_asuransi == 99)
                ? "Nama/No Asuransi : {$this->no_asuransi}"
                : "No Asuransi : {$this->no_asuransi}")
            : '';
    }
}
