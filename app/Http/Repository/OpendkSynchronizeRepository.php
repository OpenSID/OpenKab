<?php

namespace App\Http\Repository;

use App\Models\OpendkSynchronize;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OpendkSynchronizeRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'kode_kecamatan',
        'nama_kecamatan',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return OpendkSynchronize::class;
    }

    public function listSinkronisasi()
    {
        return QueryBuilder::for($this->model())
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($q) use ($value) {
                        $q->orWhere('kode_kecamatan', 'LIKE', '%'.$value.'%');
                        $q->orWhere('nama_kecamatan', 'LIKE', '%'.$value.'%');
                    });
                }),
            ])->allowedSorts($this->getFieldsSearchable())
            ->jsonPaginate();
    }
}
