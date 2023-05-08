<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends BaseModel
{
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'setting_aplikasi';
}
