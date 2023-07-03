<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    /** {@inheritdoc} */
    protected $table = 'team';

    /**
     * The fillable with the model.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'menu',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'menu' => 'json',
    ];
}
