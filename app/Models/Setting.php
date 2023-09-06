<?php

namespace App\Models;

use App\Models\OpenKabModel as Model;

class Setting extends Model
{
    public $table = 'settings';

    public $fillable = [
        'key',
        'name',
        'value',
        'type',
        'attribute',
        'description',
    ];

    protected $casts = [
        'key' => 'string',
        'name' => 'string',
        'value' => 'string',
        'type' => 'string',
        'attribute' => 'array',
        'description' => 'string',
    ];

    public static array $rules = [
        'key' => 'required|string|max:50|unique:settings,key',
        'name' => 'required|string|max:255',
        'value' => 'required|string|max:255',
        'type' => 'nullable|string|max:255',
        'attribute' => 'nullable|string|max:255',
        'description' => 'nullable|string|max:255',
    ];
}
