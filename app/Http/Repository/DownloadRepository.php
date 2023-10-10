<?php

namespace App\Http\Repository;

use App\Models\CMS\Download;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DownloadRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title',
        'url',
        'description',
        'state',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Download::class;
    }

    public function listDownload()
    {
        return QueryBuilder::for(Download::with(['counter']))
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->orWhere('title', 'LIKE', '%'.$value.'%');
                        $q->orWhere('description', 'LIKE', '%'.$value.'%');
                    });
                }),
            ])->allowedSorts($this->getFieldsSearchable())
            ->jsonPaginate();
    }

    public function publicDownload()
    {
        return QueryBuilder::for(Download::with(['counter']))->get();
    }
}
