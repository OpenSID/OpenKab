<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KB extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'tweb_cara_kb';
}
