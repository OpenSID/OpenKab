@if($config->options->localized)
    Session::flash('success',__('messages.deleted', ['model' => __('models/{{ $config->modelNames->camelPlural }}.singular')]));
@else
    Session::flash('success','{{ $config->modelNames->human }} berhasil dihapus.');
@endif
