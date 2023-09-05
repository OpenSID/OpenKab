@if($config->options->localized)
    Session::flash('success',__('messages.updated', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')]));
@else
    Session::flash('success','{{ $config->modelNames->human }} updated successfully.');
@endif
