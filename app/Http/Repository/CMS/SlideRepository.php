<?php

namespace App\Http\Repository\CMS;

use App\Http\Repository\BaseRepository;
use App\Models\CMS\Slide;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SlideRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title',
        'url',
        'thumbnail',
        'description',
        'state',
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
                    $query->where(function ($q) use ($value) {
                        $q->orWhere('title', 'LIKE', '%'.$value.'%');
                        $q->orWhere('url', 'LIKE', '%'.$value.'%');
                        $q->orWhere('description', 'LIKE', '%'.$value.'%');
                    });
                }),
            ])->allowedSorts($this->getFieldsSearchable())
            ->jsonPaginate();
    }
}
