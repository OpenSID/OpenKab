<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogPerubahanPenduduk extends Model
{
    use HasFactory;

    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /**
     * {@inheritdoc}
     */
    protected $table = 'log_perubahan_penduduk';

    /**
     * {@inheritdoc}
     */
    protected $guarded = [];

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;
}
