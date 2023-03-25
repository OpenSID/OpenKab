<?php

namespace App\Models\Gabungan;

use Illuminate\Support\Str;
use App\Models\Gabungan\Cacat;
use App\Models\Gabungan\CaraKB;
use App\Models\Gabungan\Pekerjaan;
use Illuminate\Support\Collection;
use App\Models\Gabungan\Pendidikan;
use App\Models\Gabungan\StatusKawin;
use App\Models\Gabungan\WargaNegara;
use App\Models\Gabungan\PendidikanKK;
use App\Models\Gabungan\SakitMenahun;
use App\Models\Gabungan\GolonganDarah;
use App\Models\Gabungan\AsuransiKesehatan;
use App\Models\Gabungan\BaseModelDBGabungan;
use App\Models\Gabungan\HubunganKepalaKeluarga;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penduduk extends BaseModelDBGabungan
{
    public $incrementing = false;
    protected $table     = 'tweb_penduduk';
    protected $fillable  = [];
    protected $guarded   = [];

    public function scopePendudukAktif($query, $did, $year)
    {
        $query->where('status_dasar', 1)
            ->whereYear('created_at', '<=', $year);

        if ($did != 'Semua') {
            $query->where('desa_id', $did);
        }
    }

    public function scopeHidup($query)
    {
        return $query->where('status_dasar', 1);
    }

    public function scopeLakiLaki($query)
    {
        return $query->where('sex', 1);
    }

    public function scopePerempuan($query)
    {
        return $query->where('sex', 2);
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @return BelongsTo
     */
    public function config()
    {
        return $this->belongsTo(Config::class, 'config_id')->withDefault();
    }

    public function jenisKelamin()
    {
        return $this->belongsTo(JenisKelamin::class, 'sex')->withDefault();
    }

    public function agama()
    {
        return $this->belongsTo(Agama::class, 'agama_id')->withDefault();
    }

    public function statusTetap()
    {
        return $this->belongsTo(StatusTetap::class, 'status')->withDefault();
    }

    public function pekerjaan()
    {
        return $this->belongsTo(Pekerjaan::class, 'pekerjaan_id')->withDefault();
    }

    public function hubunganKepalaKeluarga()
    {
        return $this->belongsTo(HubunganKepalaKeluarga::class, 'kk_level')->withDefault();
    }

    public function pendidikanKK()
    {
        return $this->belongsTo(PendidikanKK::class, 'pendidikan_kk_id')->withDefault();
    }

    public function pendidikanSedang()
    {
        return $this->belongsTo(Pendidikan::class, 'pendidikan_sedang_id')->withDefault();
    }

    public function statusKawin()
    {
        return $this->belongsTo(StatusKawin::class, 'status_kawin')->withDefault();
    }

    public function wargaNegara()
    {
        return $this->belongsTo(WargaNegara::class, 'warganegara_id')->withDefault();
    }

    public function golonganDarah()
    {
        return $this->belongsTo(GolonganDarah::class, 'golongan_darah_id')->withDefault();
    }

    public function cacat()
    {
        return $this->belongsTo(Cacat::class, 'cacat_id')->withDefault();
    }

    public function sakitMenahun()
    {
        return $this->belongsTo(SakitMenahun::class, 'sakit_menahun_id')->withDefault();
    }

    public function caraKB()
    {
        return $this->belongsTo(CaraKB::class, 'cara_kb_id')->withDefault();
    }

    public function asuransiKesehatan()
    {
        return $this->belongsTo(AsuransiKesehatan::class, 'id_asuransi')->withDefault();
    }

}
