<!-- Key Field -->
<div class="form-group">
    {!! Form::label('key', 'Kode Kunci:') !!}
    {!! Form::text('key', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'readonly' => 'readonly']) !!}
</div>


<!-- Name Field -->
<div class="form-group">
    {!! Form::label('name', 'Nama:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 255]) !!}
</div>

<!-- Name Field -->
<div class="form-group">
    {!! Form::label('value', 'Status:') !!}
    @switch($setting->type)
        @case('dropdown')
            {!! Form::select('value', collect($setting->attribute)->pluck('text', 'value'), $setting->value, ['class' => 'form-control', 'required']) !!}
            @break
        @default
    @endswitch
</div>


<!-- Description Field -->
<div class="form-group">
    {!! Form::label('description', 'Deskripsi:') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3 , 'maxlength' => 255]) !!}
</div>
