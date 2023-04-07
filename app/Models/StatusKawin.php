<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusKawin extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'tweb_penduduk_kawin';
}
