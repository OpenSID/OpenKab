<?php

namespace App\Http\Transformers;

use App\Models\Employee;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;

class EmployeeTransformer extends TransformerAbstract
{
    public function transform(Employee $employee)
    {
        return [
            'id' => $employee->id,
            'name' => $employee->name,
            'identity_number' => $employee->identity_number,
            'email' => $employee->email,
            'description' => $employee->description,
            'phone' => $employee->phone,
            'foto' => $employee->foto ? '<img src="'.Storage::url($employee->foto).'" class="img-thumbnail" alt="foto">' : '',
            'position_id' => $employee->position?->name,
            'department_id' => $employee->department?->name,
            'user_id' => $employee->user?->name,
            'created_at' => $employee->created_at,
            'updated_at' => $employee->updated_at,
            'deleted_at' => $employee->deleted_at,
        ];
    }
}
