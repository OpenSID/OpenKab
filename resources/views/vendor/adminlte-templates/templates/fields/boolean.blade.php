<!-- 'Boolean {{ $fieldTitle }} Field' checked by default -->
<div class="form-group row">
    <div class="col-3">
        @if($config->options->localized)
        @{!! Form::label('{{ $fieldName }}',
        __('models/{{ $config->modelNames->camelPlural }}.fields.{{ $fieldName }}').':') !!}
        @else
        @{!! Form::label('{{ $fieldName }}', '{{ $fieldTitle }}:') !!}
        @endif
    </div>
    <div class="col-9">
        <label class="checkbox-inline">
            @{!! Form::checkbox('{{ $fieldName }}', 1, true) !!}
            <!-- remove {, true} to make it unchecked by default -->
        </label>
    </div>
</div>
