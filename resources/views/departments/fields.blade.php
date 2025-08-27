<!-- Name Field -->
<div class="form-group">
    {!! Html::label('Nama:', 'name') !!}
    {!! Html::text('name', old('name', $department->name ?? ''))->class('form-control')->attribute('required')->attribute('maxlength', 50) !!}
</div>


<!-- Description Field -->
<div class="form-group">
    {!! Html::label('Deskripsi:', 'description') !!}
    {!! Html::text('description', old('description', $department->description ?? ''))->class('form-control')->attribute('required')->attribute('maxlength', 255) !!}
</div>


<!-- Parent Id Field -->
<div class="form-group">
    {!! Html::label('Dibawah Departemen:', 'parent_id') !!}
    {!! Html::select('parent_id', $parents)->class('form-control select2') !!}
</div>
