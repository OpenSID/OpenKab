<?php

namespace App\Models;

use App\Models\Traits\ConfigIdTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends BaseModel
{
    use HasFactory;
    use ConfigIdTrait;

    public const ENABLE = 1;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kategori';

    /**
     * The timestamps for the model.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Scope a query to only enable category.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeEnable($query)
    {
        return $query->where('enabled', static::ENABLE);
    }

    public function config()
    {
        return $this->hasOne(Config::class, 'id', 'config_id');
    }
}
