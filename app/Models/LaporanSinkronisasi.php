<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaporanSinkronisasi extends BaseModel
{
    use HasFactory;

    /** {@inheritdoc} */
    protected $table = 'laporan_sinkronisasi';

    protected $guarded = [];

    /**
     * Get the phone associated with the config.
     */
    public function config()
    {
        return $this->hasOne(Config::class, 'id', 'config_id');
    }

    protected function scopeTipeLaporanPenduduk($query)
    {
        return $query->where('tipe', 'laporan_penduduk');
    }

    protected function scopeApbdes($query)
    {
        return $query->where('tipe', 'laporan_apbdes');
    }
}
