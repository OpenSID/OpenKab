<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

class Keuangan extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'keuangan';

    /**
     * Get the template associated with the Keuangan.
     */
    public function template(): HasOne
    {
        return $this->hasOne(KeuanganTemplate::class, 'uuid', 'template_uuid');
    }
}
