<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role;

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

    public function role(): HasMany
    {
        return $this->hasMany(Role::class, 'team_id', 'id');
    }
}
