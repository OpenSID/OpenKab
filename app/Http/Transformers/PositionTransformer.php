<?php

namespace App\Http\Transformers;

use App\Models\Position;
use League\Fractal\TransformerAbstract;

class PositionTransformer extends TransformerAbstract
{
    public function transform(Position $position)
    {
        return [
            'id' => $position->id,
            'name' => $position->name,
            'description' => $position->description,
            'parent_id' => $position->parent?->name,
            'created_at' => $position->created_at,
            'updated_at' => $position->updated_at,
            'deleted_at' => $position->deleted_at,
        ];
    }
}
