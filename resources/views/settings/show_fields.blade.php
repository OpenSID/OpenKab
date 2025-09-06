<!-- Key Field -->
<div class="col-sm-12">
    {!! Html::label('Kunci:', 'key') !!}
    <p>{{ $setting->key }}</p>
</div>

<!-- Name Field -->
<div class="col-sm-12">
    {!! Html::label('Nama:', 'name') !!}
    <p>{{ $setting->name }}</p>
</div>

<!-- Value Field -->
<div class="col-sm-12">
    {!! Html::label('Nilai:', 'value') !!}
    <p>{{ $setting->value }}</p>
</div>

<!-- Type Field -->
<div class="col-sm-12">
    {!! Html::label('Tipe:', 'type') !!}
    <p>{{ $setting->type }}</p>
</div>

<!-- Attribute Field -->
<div class="col-sm-12">
    {!! Html::label('Atribut:', 'attribute') !!}
    <p>{{ $setting->attribute }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Html::label('Deskripsi:', 'description') !!}
    <p>{{ $setting->description }}</p>
</div>
