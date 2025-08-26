<!-- Nama Field -->
<div class="form-group col-sm-6">
    {!! Html::label('name', 'Nama:') !!}
    {!! Html::text('name', old('name', $position->name ?? ''))->class('form-control')->attribute('required')->attribute('maxlength', 50) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#name').focus()
    </script>
@endpush

<!-- Description Field -->
<div class="form-group col-sm-6">
    {!! Html::label('description', 'Deskripsi:') !!}
    {!! Html::text('description', old('description', $position->description ?? ''))->class('form-control')->attribute('required')->attribute('maxlength', 255) !!}
</div>

<!-- Parent Id Field -->
<div class="form-group col-sm-6">
    {!! Html::label('parent_id', 'Jabatan Atasan:') !!}
    {!! Html::select('parent_id', $parents)->class('form-control select2') !!}
</div>
