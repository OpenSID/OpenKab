<?php

namespace App\Http\Repository;

use App\Models\Employee;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'identity_number',
        'email',
        'description',
        'phone',
        'foto',
        'position_id',
        'department_id',
        'user_id',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Employee::class;
    }

    public function listEmployee()
    {
        return QueryBuilder::for(Employee::with(['department', 'position']))
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->orWhere('name', 'LIKE', '%'.$value.'%');
                        $q->orWhere('identity_number', 'LIKE', '%'.$value.'%');
                        $q->orWhere('email', 'LIKE', '%'.$value.'%');
                        $q->orWhere('description', 'LIKE', '%'.$value.'%');
                        $q->orWhere('phone', 'LIKE', '%'.$value.'%');
                        $q->orWhere('foto', 'LIKE', '%'.$value.'%');
                        $q->orWhere('position_id', 'LIKE', '%'.$value.'%');
                        $q->orWhere('department_id', 'LIKE', '%'.$value.'%');
                        $q->orWhere('user_id', 'LIKE', '%'.$value.'%');
                    });
                }),
            ])->allowedSorts($this->getFieldsSearchable())
            ->jsonPaginate();
    }
}
