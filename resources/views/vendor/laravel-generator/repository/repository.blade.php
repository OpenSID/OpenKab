@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ $config->namespaces->repository }};

use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};
use {{ $config->namespaces->repository }}\BaseRepository;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class {{ $config->modelNames->name }}Repository extends BaseRepository
{
    protected $fieldSearchable = [
        {!! $fieldSearchable !!}
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return {{ $config->modelNames->name }}::class;
    }

    public function list{{ $config->modelNames->name }}()
    {
        return QueryBuilder::for($this->model())
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function($q) use ($value) {
                        @foreach ($config->fields as $field)
                        @if($config->primaryName == $field->name) @continue @endif
                        $q->orWhere('{{ $field->name }}', 'LIKE', '%'.$value.'%');
                    @endforeach
                    });
                }),
            ])->allowedSorts($this->getFieldsSearchable())
            ->jsonPaginate();
    }
}
