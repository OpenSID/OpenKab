<?php

namespace App\Models;

use App\Models\Traits\FilterWilayahTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property \App\Models\Enums\TempatDilahirkanEnum  $tempat_dilahirkan
 * @property \App\Models\Enums\JenisKelahiranEnum    $jenis_kelahiran
 * @property \App\Models\Enums\PenolongKelahiranEnum $penolong_kelahiran
 * @property \Carbon\Carbon                          $tanggallahir
 */
class Penduduk extends BaseModel
{
    use FilterWilayahTrait;
    use HasFactory;

    public const KATEGORI_STATISTIK = [
        'rentang-umur' => 'Rentang Umur',
        'kategori-umur' => 'Kategori Umur',
        'pendidikan-dalam-kk' => 'Pendidikan Dalam KK',
        'pendidikan-sedang-ditempuh' => 'Pendidikan Sedang Ditempuh',
        'pekerjaan' => 'Pekerjaan',
        'status-perkawinan' => 'Status Perkawinan',
        'agama' => 'Agama',
        'jenis-kelamin' => 'Jenis Kelamin',
        'hubungan-dalam-kk' => 'Hubungan Dalam KK',
        'warga-negara' => 'Warga Negara',
        'status-penduduk' => 'Status Penduduk',
        'golongan-darah' => 'Golongan Darah',
        'penyandang-cacat' => 'Penyandang Cacat',
        'penyakit-menahun' => 'Penyakit Menahun',
        'akseptor-kb' => 'Akseptor KB',
        'akta-kelahiran' => 'Akta Kelahiran',
        'ktp' => 'Kepemilikan KTP',
        'asuransi-kesehatan' => 'Asuransi Kesehatan',
        'status-covid' => 'Status Covid',
        'suku' => 'Suku / Etnis',
        'bpjs-ketenagakerjaan' => 'BPJS Ketenagakerjaan',
        'status-kehamilan' => 'Status Kehamilan',
    ];

    /** {@inheritdoc} */
    protected $table = 'tweb_penduduk';

    /** {@inheritdoc} */
    protected $fillable = [
        'email',
    ];

    /** {@inheritdoc} */
    protected $appends = [
        'namaTempatDilahirkan',
        'namaJenisKelahiran',
        'namaPenolongKelahiran',
        'wajibKTP',
        'elKTP',
        'statusPerkawinan',
        'statusHamil',
        'namaAsuransi',
        'umur',
        'tanggalLahirId',
        'urlFoto',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'tanggallahir' => 'datetime:d-m-Y',
        'tanggal_peristiwa' => 'datetime:d-m-Y',
        'created_at' => 'datetime:d-m-Y',
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
    public function bahasa()
    {
        return $this->belongsTo(Bahasa::class, 'bahasa_id')->withDefault();
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
     * Get the phone associated with the config.
     */
    public function config()
    {
        return $this->hasOne(Config::class, 'id', 'config_id');
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
    public function statusRekamKtp()
    {
        return $this->belongsTo(Ktp::class, 'status_rekam')->withDefault();
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
    public function pendudukStatusDasar()
    {
        return $this->belongsTo(PendudukStatusDasar::class, 'status_dasar')->withDefault();
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
        return $this->hasMany(LogPenduduk::class, 'id_pend')->selectRaw('max(id) as id');
    }

    public function logPerubahanPenduduk()
    {
        return $this->hasMany(LogPerubahanPenduduk::class, 'id_pend');
    }

    public function dokumenHidup()
    {
        return $this->hasMany(DokumenHidup::class, 'id_pend');
    }

    /**
     * Getter tempat dilahirkan attribute.
     *
     * @return string
     */
    public function getNamaTempatDilahirkanAttribute()
    {
        return match ($this->tempat_dilahirkan) {
            1 => 'RS/RB',
            2 => 'Puskesmas',
            3 => 'Polindes',
            4 => 'Rumah',
            5 => 'Lainnya',
            default => null
        };
    }

    /**
     * Getter tempat dilahirkan attribute.
     *
     * @return string
     */
    public function getNamaJenisKelahiranAttribute()
    {
        return match ($this->jenis_kelahiran) {
            1 => 'Tunggal',
            2 => 'Kembar 2',
            3 => 'Kembar 3',
            4 => 'Kembar 4',
            default => null,
        };
    }

    /**
     * Getter tempat dilahirkan attribute.
     *
     * @return string
     */
    public function getNamaPenolongKelahiranAttribute()
    {
        return match ($this->penolong_kelahiran) {
            1 => 'Dokter',
            2 => 'Bidan Perawat',
            3 => 'Dukun',
            4 => 'Lainnya',
            default => null,
        };
    }

    public function getElKTPAttribute()
    {
        return match ($this->ktp_el) {
            1 => 'BELUM',
            2 => 'KTP-EL',
            3 => 'KIA',
            default => null,
        };
    }

    /**
     * Getter wajib ktp attribute.
     *
     * @return string
     */
    public function getWajibKTPAttribute()
    {
        return (($this->tanggallahir && $this->tanggallahir->age > 16) || (! empty($this->status_kawin) && $this->status_kawin != 1))
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

    /**
     * Getter umur attribute.
     *
     * @return string|null
     */
    public function getUmurAttribute()
    {
        return $this->tanggallahir?->age;
    }

    /**
     * Getter tanggal lahir indonesia attribute.
     *
     * @return string|null
     */
    public function getTanggalLahirIdAttribute()
    {
        return $this->tanggallahir?->format('d F Y');
    }

    /**
     * Getter url foto attribute.
     *
     * @return string
     */
    public function getUrlFotoAttribute()
    {
        return null;
        if (empty($this->foto)) {
            return $this->sex === 1
                ? Storage::disk("ftp_{$this->config_id}")?->url('assets/images/pengguna/kuser.png')
                : Storage::disk("ftp_{$this->config_id}")?->url('assets/images/pengguna/wuser.png');
        }

        return Storage::disk("ftp_{$this->config_id}")?->url("desa/upload/user_pict/{$this->foto}");
    }

    /**
     * Scope query untuk status penduduk.
     *
     * @param Builder $query
     * @param mixed   $value
     *
     * @return Builder
     */
    public function scopeStatus($query, $value = 1)
    {
        return $query->where('status_dasar', $value);
    }

    /**
     * Scope query untuk jenis kelamin penduduk.
     *
     * @param Builder $query
     * @param mixed   $value
     *
     * @return Builder
     */
    public function scopeJenisKelamin($query, $value = null)
    {
        if (is_null($value)) {
            return $query;
        }

        return $query->where('sex', $value);
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
            ->where('tweb_penduduk.status_dasar', 1);
    }

    public function scopeCountStatistikSuku($query)
    {
        return $this->scopeConfigId($query)
            ->select(['suku AS id', 'suku AS nama'])
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->where('tweb_penduduk.status_dasar', 1)
            ->groupBy('suku')
            ->whereNotNull('suku')
            ->where('suku', '!=', '');
    }

    /**
     * Scope untuk memanggil relasi tabel referensi.
     */
    public function scopeWithRef($query)
    {
        return $query->with([
            'jenisKelamin',
            'agama',
            'bahasa',
            'config',
            'pendidikan',
            'pendidikanKK',
            'pekerjaan',
            'wargaNegara',
            'golonganDarah',
            'cacat',
            'sakitMenahun',
            'kb',
            'statusKawin',
            'statusRekamKtp',
            'pendudukHubungan',
            'pendudukStatus',
            'pendudukStatusDasar',
            'keluarga',
            'rtm',
            'clusterDesa',
            'logPenduduk',
            'logPerubahanPenduduk',
        ]);
    }
}
