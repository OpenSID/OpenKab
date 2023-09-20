<?php

namespace App\Models\CMS;

use App\Models\OpenKabModel as Model;

class CounterDownload extends Model
{

    public $table = 'counter_downloads';

    public $fillable = [
        'model_type',
        'model_id',
        'url',
        'total'
    ];

    protected $casts = [
        'model_type' => 'string',
        'model_id' => 'integer',
        'url' => 'string',
        'total' => 'integer'
    ];

    public static array $rules = [
        'model_type' => 'required',
        'model_id' => 'required',
        'url' => 'nullable|string|max:255'
    ];
}
