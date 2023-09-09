<!-- {{ $fieldTitle }} Field -->
<div class="form-group row">
    <div class="form-check">
        @{!! Form::hidden('{{ $fieldName }}', 0, ['class' => 'form-check-input']) !!}
        @{!! Form::checkbox('{{ $fieldName }}', '{{ $checkboxVal }}', null, ['class' => 'form-check-input']) !!}
        @{!! Form::label('{{ $fieldName }}', '{{ $fieldTitle }}', ['class' => 'form-check-label']) !!}
    </div>
</div>
