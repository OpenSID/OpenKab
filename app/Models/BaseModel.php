<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';
}
