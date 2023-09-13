<?php

namespace App\Http\Transformers;

use App\Models\Department;
use League\Fractal\TransformerAbstract;

class DepartmentTransformer extends TransformerAbstract
{
    public function transform(Department $department)
    {
        return [
            'id' => $department->id,
            'name' => $department->name,
            'description' => $department->description,
            'parent_id' => $department->parent?->name,
            'created_at' => $department->created_at,
            'updated_at' => $department->updated_at,
            'deleted_at' => $department->deleted_at,
        ];
    }
}
