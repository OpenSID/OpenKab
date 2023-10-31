<?php

namespace App\Models\CMS;

use App\Models\OpenKabModel as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Slide extends Model
{
    use SoftDeletes;

    public $table = 'slides';

    public $fillable = [
        'title',
        'url',
        'thumbnail',
        'description',
        'state',
    ];

    protected $casts = [
        'title' => 'string',
        'url' => 'string',
        'thumbnail' => 'string',
        'description' => 'string',
        'state' => 'boolean',
    ];

    public static array $rules = [
        'title' => 'required|string|max:255',
        'url' => 'nullable|url|max:255',
        'description' => 'nullable|string|max:65535',
        'state' => 'required|numeric|digits_between:0,1',
        'foto' => 'nullable|image|max:1024|mimes:png,jpg',
    ];
}
