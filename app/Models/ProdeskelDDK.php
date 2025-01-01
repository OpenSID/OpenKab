<?php

namespace App\Models;

use App\Models\Traits\FilterWilayahTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdeskelDDK extends BaseModel
{
    use FilterWilayahTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prodeskel_ddk';

    protected $with = [
    ];

    /**
     * The guarded with the model.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
    ];

    /**
     * Define a one-to-one relationship.
     */
    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class, 'keluarga_id');
    }

    public function produksi()
    {
        return $this->hasMany(ProdeskelDDKProduksi::class, 'prodeskel_ddk_id', 'id');
    }

    public function detail()
    {
        return $this->hasMany(ProdeskelDDKDetail::class, 'prodeskel_ddk_id', 'id');
    }

    public function bahanGalianAnggota()
    {
        return $this->hasMany(ProdeskelDDKBahanGalianAnggota::class, 'prodeskel_ddk_id', 'id');
    }

    public function getKepalaKeluargaAttribute()
    {
        $this->loadMissing([
            'keluarga.kepalaKeluarga' => static function ($builder): void {
                $builder->withoutDefaultRelations();
            },
        ]);

        return $this->keluarga->kepalaKeluarga;
    }

    /**
     * @return detailKeluarga[kode_field] => item
     */
    public function getDetailKeluargaAttribute()
    {
        $this->loadMissing(['detail']);

        return $this->detail->whereNull('penduduk_id')->keyBy('kode_field');
    }

    /**
     * @return detailAnggota[penduduk_id][kode_field] => item
     * */
    public function getDetailAnggotaAttribute()
    {
        $this->loadMissing(['detail']);

        return $this->detail->whereNotNull('penduduk_id')
            ->groupBy('penduduk_id')
            ->transform(static fn ($item, $key) => $item->keyBy('kode_field'));
    }

    public function getJenisKelaminKepalaKeluargaAttribute()
    {
        $this->loadMissing([
            'keluarga.kepalaKeluarga' => static function ($builder): void {
                $builder->withoutDefaultRelations();
            },
        ]);

        return $this->keluarga->kepalaKeluarga->sex;
    }

    public function getNikKKAttribute()
    {
        $this->loadMissing([
            'keluarga.kepalaKeluarga' => static function ($builder): void {
                $builder->withoutDefaultRelations();
            },
        ]);

        return $this->keluarga->kepalaKeluarga->nik;
    }

    public function getNoKKAttribute()
    {
        return $this->getKepalaKeluargaAttribute()->keluarga->no_kk;
    }
}
