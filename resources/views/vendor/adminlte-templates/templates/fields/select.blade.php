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
    @{!! Form::select('{{ $fieldName }}', @php echo htmlspecialchars_decode($selectValues) @endphp, null, ['class' => 'form-control custom-select']) !!}
    </div>
</div>
