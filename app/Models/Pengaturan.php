<?php

namespace App\Models;

class Pengaturan extends BaseModel
{
    public $timestamps = false;

    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'setting_aplikasi';
}
