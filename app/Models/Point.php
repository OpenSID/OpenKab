<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Point extends BaseModel
{
    public const LOCK = 1;

    public const UNLOCK = 2;

    public const ROOT = 0;

    public const CHILD = 2;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'point';

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'simbol',
        'enabled',
        'tipe',
        'parrent',
        'sumber',
    ];

    // append
    protected $appends = [
        'path_simbol',
    ];

    protected function scopeRoot($query)
    {
        return $query->whereTipe(self::ROOT);
    }

    protected function scopeChild($query, int $parent)
    {
        return $query->whereTipe(self::CHILD)->whereParrent($parent);
    }

    protected function scopeSubPoint($query)
    {
        return $query->whereTipe(self::CHILD);
    }

    protected function scopeActive($query)
    {
        return $query->whereEnabled(self::UNLOCK);
    }

    public function isLock(): bool
    {
        return $this->enabled == self::LOCK;
    }

    /**
     * Getter untuk path + simbol.
     */
    public function getPathSimbolAttribute(): string
    {
        $simbol = $this->attributes['simbol'];

        if (empty($this->attributes['simbol'])) {
            return 'default.png';
        }

        return $simbol;
    }

    /**
     * Get the parent that owns the Polygon.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Point::class, 'parrent', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Point::class, 'parrent', 'id')->whereTipe(self::CHILD);
    }
}
