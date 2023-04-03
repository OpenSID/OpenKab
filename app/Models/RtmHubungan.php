<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RtmHubungan extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'tweb_rtm_hubungan';
}