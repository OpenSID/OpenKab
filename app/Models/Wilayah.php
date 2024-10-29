<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wilayah extends BaseModel
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tweb_wil_clusterdesa';

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
     * Scope query untuk dusun.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeDusun($query)
    {
        return $query->where('rt', '=', '0')->where('rw', '=', '0');
    }

    /**
     * Scope query untuk rw.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeRw($query)
    {
        return $query->where('rt', '=', '0')->where('rw', '!=', '0');
    }

    /**
     * Scope query untuk rt.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeRt($query)
    {
        return $query->where('rt', '!=', '0');
    }

    /**
     * Define a one-to-many relationship.
     *
     * @return HasMany
     */
    public function kepala()
    {
        return $this->hasOne(Penduduk::class, 'id', 'id_kepala')->select('nik', 'nama', 'id');
    }

    public function isDusun(): bool
    {
        return $this->attributes['rt'] == '0' && $this->attributes['rw'] == '0';
    }

    public function isRw(): bool
    {
        return $this->attributes['rt'] == '0' && $this->attributes['rw'] !== '0';
    }

    public function isRt(): bool
    {
        return $this->attributes['rt'] !== '0';
    }

    public function bukanRT(): bool
    {
        return $this->attributes['rt'] == '0';
    }

    public static function treeAccess()
    {
        return self::select(['id', 'dusun', 'rt', 'rw'])->get()->groupBy('dusun')->map(static fn ($item) => $item->filter(static fn ($q): bool => $q->rw !== '0')->groupBy('rw')->map(static fn ($item) => $item->filter(static fn ($q): bool => ! $q->isDusun() && ! $q->bukanRT()  )));
    }

}
