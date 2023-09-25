<?php

namespace App\Http\Transformers;

use App\Models\CMS\Menu;
use League\Fractal\TransformerAbstract;

class MenuTransformer extends TransformerAbstract
{
    public function transform(Menu $menu)
    {
        return [
            'id' => $menu->id,
            'name' => $menu->name,
            'url' => $menu->url,
            'sequence' => $menu->sequence,
            'position' => $menu->position,
            'parent_id' => $menu->parent_id,
            'created_at' => $menu->created_at,
            'updated_at' => $menu->updated_at,
        ];
    }
}
