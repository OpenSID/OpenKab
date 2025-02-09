<?php

namespace App\Models;

use App\Models\Traits\FilterWilayahTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Keluarga extends BaseModel
{
    use FilterWilayahTrait;

    public const SASARAN_PENDUDUK = 1;

    public const SASARAN_KELUARGA = 2;

    public const SASARAN_RUMAH_TANGGA = 3;

    public const SASARAN_KELOMPOK = 4;

    public const KATEGORI_STATISTIK = [
        'kelas-sosial' => 'Kelas Sosial',
    ];

    /** {@inheritdoc} */
    protected $table = 'tweb_keluarga';

    const CREATED_AT = null;

    /**
     * {@inheritDoc}
     */
    protected $with = [
        'wilayah',
    ];

    /**
     * Define a one-to-one relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function kepalaKeluarga()
    {
        return $this->hasOne(Penduduk::class, 'id', 'nik_kepala');
    }

    /**
     * Define a one-to-many relationship.
     *
     * @return HasMany
     */
    public function anggota()
    {
        return $this->hasMany(Penduduk::class, 'id_kk')->orderBy('kk_level')->orderBy('tanggallahir');
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function Wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'id_cluster');
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function ProdeskelDDK()
    {
        return $this->hasOne(ProdeskelDDK::class, 'keluarga_id');
    }

    /**
     * Scope untuk Statistik.
     */
    public function scopeCountStatistik($query, $configId = null)
    {
        $this->appends = [];
        $this->with = [];

        $query = $this->scopeConfigId($query)
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 1 THEN tweb_penduduk.id END) AS laki_laki')
            ->selectRaw('COUNT(CASE WHEN tweb_penduduk.sex = 2 THEN tweb_penduduk.id END) AS perempuan')
            ->join('tweb_penduduk', 'tweb_penduduk.id', '=', "{$this->table}.nik_kepala", 'left')
            ->join('config', 'config.id', '=', "{$this->table}.config_id", 'left')
            ->join('program', 'program.config_id', '=', 'config.id', 'left');
        if (isset(request('filter')['tahun'])) {
            $query->whereRaw('YEAR(program.sdate) = '.request('filter')['tahun']);
        }
        $query->where('tweb_penduduk.status_dasar', 1);
        if (isset(request('filter')['kabupaten'])) {
            $query->whereRaw('config.kode_kabupaten = '.request('filter')['kabupaten']);
        }
        if (isset(request('filter')['kecamatan'])) {
            $query->whereRaw('config.kode_kecamatan = '.request('filter')['kecamatan']);
        }
        if (isset(request('filter')['desa'])) {
            $query->whereRaw('config.kode_desa = '.request('filter')['desa']);
        }
        $query->whereRaw('program.sasaran = '.self::SASARAN_KELUARGA);

        if ($configId) {
            $query->where('tweb_keluarga.config_id', $configId);
        }

        return $query;
    }

    /**
     * Scope untuk status keluarga berdasarkan penduduk hidup.
     */
    public function scopeStatus($query, $value = 1)
    {
        return $query->whereHas('kepalaKeluarga', static function ($query) use ($value) {
            $query->status($value)->where('kk_level', '1');
        });
    }

    /**
     * Get the phone associated with the config.
     */
    public function config()
    {
        return $this->hasOne(Config::class, 'id', 'config_id');
    }
}
