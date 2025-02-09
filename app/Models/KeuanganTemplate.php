<?php

namespace App\Models;

class KeuanganTemplate extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'keuangan_template';

    protected function scopeApbdes($query)
    {
        return $query->whereRaw('length(parent_uuid) = 5');
    }
}
