<?php

namespace App\Models;

use App\Models\Traits\FilterWilayahTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdeskelDDKDetail extends BaseModel
{
    use FilterWilayahTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prodeskel_ddk_detail';

    /**
     * The guarded with the model.
     *
     * @var array
     */
    protected $guarded = [];

    protected $touches = ['ddk'];

    protected $casts = [
        'created_at' => 'date:Y-m-d H:i:s',
        'updated_at' => 'date:Y-m-d H:i:s',
    ];

    /**
     * Define a one-to-one relationship.
     */
    public function ddk(): BelongsTo
    {
        return $this->belongsTo(ProdeskelDDK::class, 'prodeskel_ddk_id');
    }

    /**
     * Define a one-to-one relationship.
     */
    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class, 'keluarga_id');
    }

    public function getValueAttribute()
    {
        return json_decode($this->attributes['value'], true);
    }
}
