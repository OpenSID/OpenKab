<?php

namespace App\Http\Transformers;

use App\Models\CMS\Category;
use App\Models\Enums\StatusEnum;
use League\Fractal\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{
    public function transform(Category $category)
    {
        return [
            'id' => $category->id,
            'slug' => $category->slug,
            'name' => $category->name,
            'status' => $category->status == StatusEnum::aktif ? 'Ya' : 'Tidak',
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,
            'deleted_at' => $category->deleted_at,
        ];
    }
}
