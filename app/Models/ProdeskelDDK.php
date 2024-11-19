<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class ProdeskelDDK extends BaseModel
{
    /** {@inheritdoc} */
    protected $table = 'prodeskel_ddk';

    /**
     * {@inheritDoc}
     */
    protected $with = [
        'config_id',
    ];
}
