<?php

namespace App\Http\Repository\CMS;

use App\Http\Repository\BaseRepository;
use App\Models\CMS\Page;
use Illuminate\Database\Eloquent\Model;
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

    public function create(array $input): Model
    {
        $input['published_at'] = date_from_format($input['published_at']);

        return parent::create($input);
    }

    /**
     * Update model record for given id.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model
     */
    public function update(array $input, int $id)
    {
        $input['published_at'] = date_from_format($input['published_at']);

        return parent::update($input, $id);
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
