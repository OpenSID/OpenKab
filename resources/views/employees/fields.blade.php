<!-- Nama Field -->
<div class="form-group col-sm-6">
    {!! Html::label('Nama:', 'name') !!}
    {!! Html::text('name', old('name', $employee->name ?? ''))->class('form-control')->attribute('required')->attribute('maxlength', 50) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#name').focus()
    </script>
@endpush

<!-- Identity Number Field -->
<div class="form-group col-sm-6">
    {!! Html::label('NIP:', 'identity_number') !!}
    {!! Html::text('identity_number', old('identity_number', $employee->identity_number ?? ''))->class('form-control')->attribute('maxlength', 20) !!}
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Html::label('Email:', 'email') !!}
    {!! Html::email('email', old('email', $employee->email ?? ''))->class('form-control')->attribute('maxlength', 255) !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-6">
    {!! Html::label('Deskripsi:', 'description') !!}
    {!! Html::text('description', old('description', $employee->description ?? ''))->class('form-control')->attribute('maxlength', 255) !!}
</div>

<!-- Phone Field -->
<div class="form-group col-sm-6">
    {!! Html::label('Telepon:', 'phone') !!}
    {!! Html::text('phone', old('phone', $employee->phone ?? ''))->class('form-control')->attribute('maxlength', 20) !!}
</div>

<!-- Position Id Field -->
<div class="form-group col-sm-6">
    {!! Html::label('Jabatan:', 'position_id') !!}
    {!! Html::select('position_id', $positions)->class('form-control select2') !!}
</div>

<!-- Department Id Field -->
<div class="form-group col-sm-6">
    {!! Html::label('Departemen:', 'department_id') !!}
    {!! Html::select('department_id', $departments, old('department_id', $employee->department_id ?? ''))->class(
        'form-control select2',
    ) !!}
</div>


<!-- Identity Number Field -->
<div class="form-group">
    {!! Html::label('NIP:', 'identity_number') !!}
    {!! Html::text('identity_number', old('identity_number', $employee->identity_number ?? ''))->class('form-control')->attribute('maxlength', 20) !!}
</div>


<!-- Email Field -->
<div class="form-group ">
    {!! Html::label('Email:', 'email') !!}
    {!! Html::email('email', old('email', $employee->email ?? ''))->class('form-control')->attribute('maxlength', 255) !!}
</div>


<!-- Description Field -->
<div class="form-group">
    {!! Html::label('Deskripsi:', 'description') !!}
    {!! Html::text('description', old('description', $employee->description ?? ''))->class('form-control')->attribute('maxlength', 255) !!}
</div>


<!-- Phone Field -->
<div class="form-group">
    {!! Html::label('Telepon:', 'phone') !!}
    {!! Html::text('phone', old('phone', $employee->phone ?? ''))->class('form-control')->attribute('maxlength', 20) !!}
</div>

<!-- Position Id Field -->
<div class="form-group">
    {!! Html::label('Jabatan:', 'position_id') !!}
    {!! Html::select('position_id', $positions, old('position_id', $employee->position_id ?? ''))->class(
        'form-control select2',
    ) !!}
</div>


<!-- Department Id Field -->
<div class="form-group">
    {!! Html::label('Departemen:', 'department_id') !!}
    {!! Html::select('department_id', $departments)->class('form-control select2') !!}
</div>
