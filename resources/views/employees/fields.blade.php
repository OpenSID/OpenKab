<!-- Name Field -->
<div class="form-group">
    {!! Form::label('name', 'Nama:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>


<!-- Identity Number Field -->
<div class="form-group">
    {!! Form::label('identity_number', 'NIP:') !!}
    {!! Form::text('identity_number', null, ['class' => 'form-control', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>


<!-- Email Field -->
<div class="form-group ">
    {!! Form::label('email', 'Email:') !!}
    {!! Form::email('email', null, ['class' => 'form-control', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>


<!-- Description Field -->
<div class="form-group">
    {!! Form::label('description', 'Deskripsi:') !!}
    {!! Form::text('description', null, ['class' => 'form-control', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>


<!-- Phone Field -->
<div class="form-group">
    {!! Form::label('phone', 'Telepon:') !!}
    {!! Form::text('phone', null, ['class' => 'form-control', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- Position Id Field -->
<div class="form-group">
    {!! Form::label('position_id', 'Jabatan:') !!}
    {!! Form::select('position_id', $positions, null, ['class' => 'form-control select2']) !!}
</div>


<!-- Department Id Field -->
<div class="form-group">
    {!! Form::label('department_id', 'Departemen:') !!}
    {!! Form::select('department_id', $departments, null, ['class' => 'form-control select2']) !!}
</div>
