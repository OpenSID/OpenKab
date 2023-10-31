<?php

namespace App\Models\CMS;

use App\Models\OpenKabModel as Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;

/**
 * App\Base\SluggableModel.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Base\SluggableModel findSimilarSlugs($attribute, $config, $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Base\SluggableModel whereSlug($slug)
 *
 * @mixin \Eloquent
 */
class SluggableModel extends Model
{
    use Sluggable, SluggableScopeHelpers;

    /**
     * @var array
     */
    protected $guarded = ['created_at', 'id'];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'onUpdate' => true,
            ],
        ];
    }
}
