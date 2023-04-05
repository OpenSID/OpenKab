<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelasSosial extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tweb_keluarga_sejahtera';
}
