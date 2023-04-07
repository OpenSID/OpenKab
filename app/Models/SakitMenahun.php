<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SakitMenahun extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'tweb_sakit_menahun';
}
