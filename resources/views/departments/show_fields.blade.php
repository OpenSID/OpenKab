<!-- Name Field -->
<div class="col-sm-12">
    {!! Html::label('Nama:', 'name') !!}
    <p>{{ $department->name }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Html::label('Deskripsi:', 'description') !!}
    <p>{{ $department->description }}</p>
</div>

<!-- Parent Id Field -->
<div class="col-sm-12">
    {!! Html::label('ID Departemen Induk:', 'parent_id') !!}
    <p>{{ $department->parent_id }}</p>
</div>
