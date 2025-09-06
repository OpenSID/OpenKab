<!-- Name Field -->
<div class="col-sm-12">
    {!! Html::label('Nama:', 'name') !!}
    <p>{{ $position->name }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Html::label('Deskripsi:', 'description') !!}
    <p>{{ $position->description }}</p>
</div>

<!-- Parent Id Field -->
<div class="col-sm-12">
    {!! Html::label('ID Jabatan Atasan:', 'parent_id') !!}
    <p>{{ $position->parent_id }}</p>
</div>
