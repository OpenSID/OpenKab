<?php

namespace App\Http\Repository\CMS;

use App\Http\Repository\BaseRepository;
use App\Models\CMS\Page;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PageRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'published_at',
        'slug',
        'title',
        'thumbnail',
        'content',
        'state',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Page::class;
    }

    public function listPage()
    {
        return QueryBuilder::for($this->model())
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->orWhere('slug', 'LIKE', '%'.$value.'%');
                        $q->orWhere('title', 'LIKE', '%'.$value.'%');
                    });
                }),
            ])->allowedSorts($this->getFieldsSearchable())
            ->jsonPaginate();
    }
}
