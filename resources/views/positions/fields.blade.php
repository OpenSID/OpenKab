<!-- Name Field -->
<div class="form-group">
    {!! Form::label('name', 'Nama:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>


<!-- Description Field -->
<div class="form-group">
    {!! Form::label('description', 'Deskripsi:') !!}
    {!! Form::text('description', null, ['class' => 'form-control', 'required', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>


<!-- Parent Id Field -->
<div class="form-group">
    {!! Form::label('parent_id', 'Jabatan Atasan:') !!}
    {!! Form::select('parent_id', $parents, null, ['class' => 'form-control select2']) !!}
</div>
