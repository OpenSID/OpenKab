<!-- Name Field -->
<div class="form-group">
    {!! Html::label('name', 'Nama:') !!}
    {!! Html::text('name')->class('form-control')->attribute('required')->attribute('maxlength', 50) !!}
</div>


<!-- Description Field -->
<div class="form-group">
    {!! Html::label('description', 'Deskripsi:') !!}
    {!! Html::text('description')->class('form-control')->attribute('required')->attribute('maxlength', 255) !!}
</div>


<!-- Parent Id Field -->
<div class="form-group">
    {!! Html::label('parent_id', 'Dibawah Departemen:') !!}
    {!! Html::select('parent_id', $parents)->class('form-control select2') !!}
</div>
