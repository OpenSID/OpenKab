<?php

namespace App\Models\CMS;

use App\Models\OpenKabModel as Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    public $table = 'menus';

    public $fillable = [
        'icon',
        'menu_type',
        'name',
        'url',
        'sequence',
        'position',
        'parent_id',
        'is_show',
    ];

    protected $casts = [
        'name' => 'string',
        'url' => 'string',
        'position' => 'string',
        'is_show' => 'boolean',
    ];

    public static array $rules = [
        'name' => 'required|string|max:255',
        'url' => 'nullable|url|max:255',
        'icon' => 'nullable|url|max:255',
        'text' => 'nullable|string|max:255',
        'href' => 'nullable|url|max:255',
        'sequence' => 'nullable',
        'position' => 'required|string',
        'parent_id' => 'nullable',
    ];

    /**
     * Get all of the children for the Menu.
     */
    public function child(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id', 'id')->with(['children' => function ($q) {
            return $q->selectRaw("id, parent_id , name as text, url as href, is_show,'fas fa-list' as icon");
        }]);
    }
}
