<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusDasar extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'tweb_status_dasar';
}
