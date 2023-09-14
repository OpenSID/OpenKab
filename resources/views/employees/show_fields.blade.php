<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $employee->name }}</p>
</div>

<!-- Identity Number Field -->
<div class="col-sm-12">
    {!! Form::label('identity_number', 'Identity Number:') !!}
    <p>{{ $employee->identity_number }}</p>
</div>

<!-- Email Field -->
<div class="col-sm-12">
    {!! Form::label('email', 'Email:') !!}
    <p>{{ $employee->email }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $employee->description }}</p>
</div>

<!-- Phone Field -->
<div class="col-sm-12">
    {!! Form::label('phone', 'Phone:') !!}
    <p>{{ $employee->phone }}</p>
</div>

<!-- Foto Field -->
<div class="col-sm-12">
    {!! Form::label('foto', 'Foto:') !!}
    <p>{{ $employee->foto }}</p>
</div>

<!-- Position Id Field -->
<div class="col-sm-12">
    {!! Form::label('position_id', 'Position Id:') !!}
    <p>{{ $employee->position_id }}</p>
</div>

<!-- Department Id Field -->
<div class="col-sm-12">
    {!! Form::label('department_id', 'Department Id:') !!}
    <p>{{ $employee->department_id }}</p>
</div>

<!-- User Id Field -->
<div class="col-sm-12">
    {!! Form::label('user_id', 'User Id:') !!}
    <p>{{ $employee->user_id }}</p>
</div>

