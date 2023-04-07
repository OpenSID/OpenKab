<?php

namespace App\Models;

use App\Models\Enums\SasaranEnum;
use Illuminate\Database\Eloquent\Model;

class BantuanPeserta extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /** {@inheritdoc} */
    protected $table = 'program_peserta';
}
