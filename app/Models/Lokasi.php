<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lokasi extends BaseModel
{
    public const LOCK = 1;

    public const UNLOCK = 2;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lokasi';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'desk',
        'nama',
        'enabled',
        'lat',
        'lng',
        'ref_point',
        'foto',
        'id_cluster',
    ];

    public function config(): BelongsTo
    {
        return $this->belongsTo(Config::class, 'config_id');
    }

    protected function scopeActive($query)
    {
        return $query->whereEnabled(1);
    }

    /**
     * Get the point associated with the Lokasi.
     */
    public function point(): HasOne
    {
        return $this->hasOne(Point::class, 'id', 'ref_point');
    }

    public function isLock(): bool
    {
        return $this->enabled == self::LOCK;
    }

    public static function activeLocationMap()
    {
        return self::active()->with(['point' => static fn ($q) => $q->select(['id', 'nama', 'parrent', 'simbol'])->with(['parent' => static fn ($r) => $r->select(['id', 'nama', 'parrent', 'simbol'])]),
        ])->get()->map(function ($item) {
            $item->jenis = $item->point->parent->nama ?? '';
            $item->kategori = $item->point->nama ?? '';
            $item->simbol = $item->point->simbol ?? '';
            unset($item->point);

            return $item;
        })->toArray();
    }
}
