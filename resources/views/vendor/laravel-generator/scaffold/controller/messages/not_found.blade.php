        if (empty(${{ $config->modelNames->camel }})) {
@if($config->options->localized)
        Session::flash('error',__('models/{{ $config->modelNames->camelPlural }}.singular').' '.__('messages.not_found'));
@else
        Session::flash('error','{{ $config->modelNames->human }} tidak ditemukan');
@endif

            return redirect(route('{{ $config->prefixes->getRoutePrefixWith('.') }}{{ $config->modelNames->camelPlural }}.index'));
        }
