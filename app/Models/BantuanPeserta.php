<?php

namespace App\Models;

use App\Models\Traits\ConfigIdTrait;

class BantuanPeserta extends BaseModel
{
    use ConfigIdTrait;

    /** {@inheritdoc} */
    protected $table = 'program_peserta';

    /** {@inheritdoc} */
    protected $appends = [
        'nik',
        'no_kk',
        'jenis_kelamin',
        'keterangan',
    ];

    public function getNikAttribute()
    {
        return $this->penduduk ? $this->penduduk->nik : null;
    }

    public function getNoKKAttribute()
    {
        return $this->penduduk ? $this->penduduk->no_kk : null;
    }

    public function getJenisKelaminAttribute()
    {
        return $this->penduduk ? $this->penduduk->jenisKelamin : null;
    }

    public function getKeteranganAttribute()
    {
        return $this->penduduk ? $this->penduduk->pendudukStatusDasar : null;
    }

    /**
     * Define a one-to-many relationship.
     *
     * @return BelongsTo
     */
    public function bantuan()
    {
        return $this->belongsTo(Bantuan::class, 'program_id');
    }

    /**
     * Define a one-to-many relationship.
     *
     * @return BelongsTo
     */
    public function penduduk()
    {
        return $this->belongsTo(Penduduk::class, 'kartu_id_pend');
    }
}
