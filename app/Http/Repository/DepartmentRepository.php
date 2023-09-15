<?php

namespace App\Http\Repository;

use App\Models\Department;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DepartmentRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'description',
        'parent_id',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Department::class;
    }

    public function listDepartment()
    {
        return QueryBuilder::for(Department::with('parent'))
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->orWhere('name', 'LIKE', '%'.$value.'%');
                        $q->orWhere('description', 'LIKE', '%'.$value.'%');
                        $q->orWhere('parent_id', 'LIKE', '%'.$value.'%');
                    });
                }),
            ])->allowedSorts($this->getFieldsSearchable())
            ->jsonPaginate();
    }
}
