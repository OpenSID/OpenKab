<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role;

class Team extends OpenKabModel
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
        'menu_order',
    ];

    /** {@inheritdoc} */
    protected $casts = [
        'menu' => 'json',
        'menu_order' => 'json',
    ];

    public function role(): HasMany
    {
        return $this->hasMany(Role::class, 'team_id', 'id');
    }
}
