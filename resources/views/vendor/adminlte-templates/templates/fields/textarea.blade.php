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
    @{!! Form::textarea('{{ $fieldName }}', null, ['class' => 'form-control'@php if(isset($options)) { echo htmlspecialchars_decode($options); } @endphp]) !!}
    </div>
</div>
