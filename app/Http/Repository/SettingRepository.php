<?php

namespace App\Http\Repository;

use App\Models\Setting;
use App\Http\Repository\BaseRepository;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SettingRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'key',
        'name',
        'value',
        'type',
        'attribute',
        'description'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Setting::class;
    }

    public function listSetting()
    {
        return QueryBuilder::for($this->model())
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function($q) use ($value) {
                                                                                                 $q->orWhere('key', 'LIKE', '%'.$value.'%');
                                                                    $q->orWhere('name', 'LIKE', '%'.$value.'%');
                                                                    $q->orWhere('value', 'LIKE', '%'.$value.'%');
                                                                    $q->orWhere('type', 'LIKE', '%'.$value.'%');
                                                                    $q->orWhere('attribute', 'LIKE', '%'.$value.'%');
                                                                    $q->orWhere('description', 'LIKE', '%'.$value.'%');
                                        });
                }),
            ])->allowedSorts($this->getFieldsSearchable())
            ->jsonPaginate();
    }
}
