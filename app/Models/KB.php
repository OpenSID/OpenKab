<?php

namespace App\Models;

use App\Models\Traits\ConfigIdTrait;
use Illuminate\Database\Eloquent\Model;

class KB extends BaseModel
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'tweb_cara_kb';
}
