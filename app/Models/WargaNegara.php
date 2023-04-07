<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WargaNegara extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'tweb_penduduk_warganegara';
}
