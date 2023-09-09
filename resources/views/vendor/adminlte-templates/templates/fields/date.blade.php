<!-- {{ $fieldTitle }} Field -->
<div class="form-group row">
    <div class="col-3">
@if($config->options->localized)
    @{!! Form::label('{{ $fieldName }}', __('models/{{ $config->modelNames->camelPlural }}.fields.{{ $fieldName }}').':') !!}
@else
    @{!! Form::label('{{ $fieldName }}', '{{ $fieldTitle }}:') !!}
@endif
    </div>
    <div class="col-9">
    @{!! Form::text('{{ $fieldName }}', null, ['class' => 'form-control datepicker','id'=>'{{ $fieldName }}']) !!}
    </div>
</div>
