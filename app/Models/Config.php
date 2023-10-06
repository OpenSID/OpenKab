<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'config';
}
