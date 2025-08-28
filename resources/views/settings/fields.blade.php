<!-- Key Field -->
<div class="form-group">
    {!! Html::label('Kode Kunci:', 'key') !!}
    {!! Html::text('key', old('key', $setting->key ?? ''))->class('form-control')->attribute('required')->attribute('maxlength', 50)->attribute('disabled') !!}
</div>


<!-- Name Field -->
<div class="form-group">
    {!! Html::label('Nama:', 'name') !!}
    {!! Html::text('name', old('name', $setting->name ?? ''))->class('form-control')->attribute('required')->attribute('maxlength', 255) !!}
</div>

<!-- Name Field -->
<div class="form-group">
    @switch($setting->type)
        @case('dropdown')
            {!! Html::label('Status:', 'value') !!}
            {!! Html::select(
                'value',
                collect($setting->attribute)->pluck('text', 'value'),
                old('value', $setting->value ?? ''),
            )->class('form-control')->attribute('required') !!}
        @break

        @default
            {!! Html::label('Nilai:', 'value') !!}
            {!! Html::text('value', old('value', $setting->value ?? ''))->class('form-control')->attribute('required')->attribute('maxlength', 255) !!}
    @endswitch
</div>


<!-- Description Field -->
<div class="form-group">
    {!! Html::label('Deskripsi:', 'description') !!}
    {!! Html::textarea('description')->class('form-control')->attribute('rows', 3)->attribute('maxlength', 255) !!}
</div>
