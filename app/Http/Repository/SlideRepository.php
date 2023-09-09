<?php

namespace App\Http\Repository;

use App\Models\Slide;
use App\Http\Repository\BaseRepository;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SlideRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title',
        'url',
        'thumbnail',
        'description',
        'state'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Slide::class;
    }

    public function listSlide()
    {
        return QueryBuilder::for($this->model())
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function($q) use ($value) {
                                                                                                 $q->orWhere('title', 'LIKE', '%'.$value.'%');
                                                                    $q->orWhere('url', 'LIKE', '%'.$value.'%');
                                                                    $q->orWhere('thumbnail', 'LIKE', '%'.$value.'%');
                                                                    $q->orWhere('description', 'LIKE', '%'.$value.'%');
                                                                    $q->orWhere('state', 'LIKE', '%'.$value.'%');
                                        });
                }),
            ])->allowedSorts($this->getFieldsSearchable())
            ->jsonPaginate();
    }
}
