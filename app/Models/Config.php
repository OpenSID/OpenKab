<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Config extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'config';
}
