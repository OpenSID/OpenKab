<!-- Nama Field -->
<div class="form-group col-sm-6">
    {!! Html::label('name', 'Nama:') !!}
    {!! Html::text('name')->class('form-control')->attribute('required')->attribute('maxlength', 50) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#name').focus()
    </script>
@endpush

<!-- Identity Number Field -->
<div class="form-group col-sm-6">
    {!! Html::label('identity_number', 'NIP:') !!}
    {!! Html::text('identity_number')->class('form-control')->attribute('maxlength', 20) !!}
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Html::label('email', 'Email:') !!}
    {!! Html::email('email')->class('form-control')->attribute('maxlength', 255) !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-6">
    {!! Html::label('description', 'Deskripsi:') !!}
    {!! Html::text('description')->class('form-control')->attribute('maxlength', 255) !!}
</div>

<!-- Phone Field -->
<div class="form-group col-sm-6">
    {!! Html::label('phone', 'Telepon:') !!}
    {!! Html::text('phone')->class('form-control')->attribute('maxlength', 20) !!}
</div>

<!-- Position Id Field -->
<div class="form-group col-sm-6">
    {!! Html::label('position_id', 'Jabatan:') !!}
    {!! Html::select('position_id', $positions)->class('form-control select2') !!}
</div>

<!-- Department Id Field -->
<div class="form-group col-sm-6">
    {!! Html::label('department_id', 'Departemen:') !!}
    {!! Html::select('department_id', $departments)->class('form-control select2') !!}
</div>


<!-- Identity Number Field -->
<div class="form-group">
    {!! Html::label('identity_number', 'NIP:') !!}
    {!! Html::text('identity_number')->class('form-control')->attribute('maxlength', 20) !!}
</div>


<!-- Email Field -->
<div class="form-group ">
    {!! Html::label('email', 'Email:') !!}
    {!! Html::email('email')->class('form-control')->attribute('maxlength', 255) !!}
</div>


<!-- Description Field -->
<div class="form-group">
    {!! Html::label('description', 'Deskripsi:') !!}
    {!! Html::text('description')->class('form-control')->attribute('maxlength', 255) !!}
</div>


<!-- Phone Field -->
<div class="form-group">
    {!! Html::label('phone', 'Telepon:') !!}
    {!! Html::text('phone')->class('form-control')->attribute('maxlength', 20) !!}
</div>

<!-- Position Id Field -->
<div class="form-group">
    {!! Html::label('position_id', 'Jabatan:') !!}
    {!! Html::select('position_id', $positions)->class('form-control select2') !!}
</div>


<!-- Department Id Field -->
<div class="form-group">
    {!! Html::label('department_id', 'Departemen:') !!}
    {!! Html::select('department_id', $departments)->class('form-control select2') !!}
</div>
