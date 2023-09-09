@php
    echo "<?php".PHP_EOL;
@endphp

namespace {{ config('laravel_generator.namespace.transformer') }};

use {{ $config->namespaces->model }}\{{ $config->modelNames->name }};
use League\Fractal\TransformerAbstract;

class {{ $config->modelNames->name }}Transformer extends TransformerAbstract
{
    public function transform({{ $config->modelNames->name }} ${{ Str::lower($config->modelNames->name) }})
    {
        return [
            {!! $transforms !!}
        ];
    }

}
