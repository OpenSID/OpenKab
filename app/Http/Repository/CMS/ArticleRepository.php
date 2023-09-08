<?php

namespace App\Http\Repository\CMS;

use App\Http\Repository\BaseRepository;
use App\Models\CMS\Article;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ArticleRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'category_id',
        'published_at',
        'slug',
        'title',
        'description',
        'content',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Article::class;
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

    public function listArticle()
    {
        return QueryBuilder::for(Article::with(['category']))
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->orWhere('slug', 'LIKE', '%'.$value.'%');
                        $q->orWhere('title', 'LIKE', '%'.$value.'%');
                        $q->orWhere('content', 'LIKE', '%'.$value.'%');
                    });
                }),
            ])->allowedSorts($this->getFieldsSearchable())
            ->jsonPaginate();
    }
}
