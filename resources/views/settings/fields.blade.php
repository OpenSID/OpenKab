<!-- Key Field -->
<div class="form-group">
    {!! Html::label('key', 'Kode Kunci:') !!}
    {!! Html::text('key')->class('form-control')->attribute('required')->attribute('maxlength', 50)->attribute('disabled') !!}
</div>


<!-- Name Field -->
<div class="form-group">
    {!! Html::label('name', 'Nama:') !!}
    {!! Html::text('name')->class('form-control')->attribute('required')->attribute('maxlength', 255) !!}
</div>

<!-- Name Field -->
<div class="form-group">
    @switch($setting->type)
        @case('dropdown')
            {!! Html::label('value', 'Status:') !!}
            {!! Html::select('value', collect($setting->attribute)->pluck('text', 'value'), $setting->value)->class('form-control')->attribute('required') !!}
        @break

        @default
            {!! Html::label('value', 'Nilai:') !!}
            {!! Html::text('value')->class('form-control')->attribute('required')->attribute('maxlength', 255) !!}
    @endswitch
</div>


<!-- Description Field -->
<div class="form-group">
    {!! Html::label('description', 'Deskripsi:') !!}
    {!! Html::textarea('description')->class('form-control')->attribute('rows', 3)->attribute('maxlength', 255) !!}
</div>
