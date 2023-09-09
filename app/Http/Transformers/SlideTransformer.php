<?php

namespace App\Http\Transformers;

use App\Models\Slide;
use League\Fractal\TransformerAbstract;

class SlideTransformer extends TransformerAbstract
{
    public function transform(Slide $slide)
    {
        return [
            'id' => $slide->id,
        'title' => $slide->title,
        'url' => $slide->url,
        'thumbnail' => $slide->thumbnail,
        'description' => $slide->description,
        'state' => $slide->state,
        'created_at' => $slide->created_at,
        'updated_at' => $slide->updated_at,
        'deleted_at' => $slide->deleted_at
        ];
    }

}
