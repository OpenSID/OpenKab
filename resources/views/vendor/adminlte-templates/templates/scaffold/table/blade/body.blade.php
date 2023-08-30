
    <div class="table-responsive">
        <table class="table" id="{{ $config->modelNames->dashedPlural }}-table">
            <thead>
            <tr>
                <th>No</th>
                {!! $fieldHeaders !!}
@if($config->options->localized)
                <th colspan="3">@lang('crud.action')</th>
@else
                <th>Aksi</th>
@endif
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

