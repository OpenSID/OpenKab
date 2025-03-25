<!-- Key Field -->
<div class="form-group">
    {!! Form::label('key', 'Kode Kunci:') !!}
    {!! Form::text('key', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'disabled' => 'disabled']) !!}
</div>


<!-- Name Field -->
<div class="form-group">
    {!! Form::label('name', 'Nama:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 255]) !!}
</div>

<!-- Name Field -->
<div class="form-group">
    @switch($setting->type)
        @case('dropdown')
            {!! Form::label('value', 'Status:') !!}
            {!! Form::select('value', collect($setting->attribute)->pluck('text', 'value'), $setting->value, ['class' => 'form-control', 'required']) !!}
            @break
        @default
            {!! Form::label('value', 'Nilai:') !!}
            {!! Form::text('value', null, ['class' => 'form-control', 'required', 'maxlength' => 255]) !!}
    @endswitch
</div>


<!-- Description Field -->
<div class="form-group">
    {!! Form::label('description', 'Deskripsi:') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3 , 'maxlength' => 255]) !!}
</div>
