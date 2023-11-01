<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends OpenKabModel
{
    use SoftDeletes;

    public $table = 'employees';

    public $fillable = [
        'name',
        'identity_number',
        'email',
        'description',
        'phone',
        'foto',
        'position_id',
        'department_id',
        'user_id',
    ];

    protected $casts = [
        'name' => 'string',
        'identity_number' => 'string',
        'email' => 'string',
        'description' => 'string',
        'phone' => 'string',
        'foto' => 'string',
    ];

    public static array $rules = [
        'name' => 'required|string|max:50',
        'identity_number' => 'nullable|string|max:20',
        'email' => 'nullable|string|max:255',
        'description' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:20',
        'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:1024|valid_file',
        'position_id' => 'nullable',
        'department_id' => 'nullable',
    ];

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    public function position(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Position::class, 'position_id');
    }
}
