<!-- Name Field -->
<div class="col-sm-12">
    {!! Html::label('Nama:', 'name') !!}
    <p>{{ $employee->name }}</p>
</div>

<!-- Identity Number Field -->
<div class="col-sm-12">
    {!! Html::label('NIP:', 'identity_number') !!}
    <p>{{ $employee->identity_number }}</p>
</div>

<!-- Email Field -->
<div class="col-sm-12">
    {!! Html::label('Email:', 'email') !!}
    <p>{{ $employee->email }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Html::label('Deskripsi:', 'description') !!}
    <p>{{ $employee->description }}</p>
</div>

<!-- Phone Field -->
<div class="col-sm-12">
    {!! Html::label('Telepon:', 'phone') !!}
    <p>{{ $employee->phone }}</p>
</div>

<!-- Foto Field -->
<div class="col-sm-12">
    {!! Html::label('Foto:', 'foto') !!}
    <p>{{ $employee->foto }}</p>
</div>

<!-- Position Id Field -->
<div class="col-sm-12">
    {!! Html::label('ID Jabatan:', 'position_id') !!}
    <p>{{ $employee->position_id }}</p>
</div>

<!-- Department Id Field -->
<div class="col-sm-12">
    {!! Html::label('ID Departemen:', 'department_id') !!}
    <p>{{ $employee->department_id }}</p>
</div>

<!-- User Id Field -->
<div class="col-sm-12">
    {!! Html::label('ID Pengguna:', 'user_id') !!}
    <p>{{ $employee->user_id }}</p>
</div>
