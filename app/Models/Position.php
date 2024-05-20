<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;

class Position extends OpenKabModel
{
    use SoftDeletes;
    use NodeTrait;

    public $table = 'positions';

    public $fillable = [
        'name',
        'description',
        'parent_id',
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
    ];

    public static array $rules = [
        'name' => 'required|string|max:50',
        'description' => 'required|string|max:255',
        'parent_id' => 'nullable',
    ];

    public function employees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Employee::class, 'position_id');
    }
}
