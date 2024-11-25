<?php

namespace App\Models;

use App\Models\Traits\FilterWilayahTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProdeskelDDKProduksi extends BaseModel
{
    use FilterWilayahTrait;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'prodeskel_ddk_produksi';

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
}
