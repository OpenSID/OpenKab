<?php

namespace App\Models;

use App\Models\OpenKabModel as Model;
 use Illuminate\Database\Eloquent\SoftDeletes;
class Slide extends Model
{
     use SoftDeletes;    public $table = 'slides';

    public $fillable = [
        'title',
        'url',
        'thumbnail',
        'description',
        'state'
    ];

    protected $casts = [
        'title' => 'string',
        'url' => 'string',
        'thumbnail' => 'string',
        'description' => 'string',
        'state' => 'boolean'
    ];

    public static array $rules = [
        'title' => 'required|string|max:255',
        'url' => 'nullable|string|max:255',
        'thumbnail' => 'nullable|string|max:255',
        'description' => 'nullable|string|max:65535',
        'state' => 'required|boolean'
    ];

    
}
