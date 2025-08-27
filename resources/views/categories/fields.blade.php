<!-- name Field -->
<div class="form-group row">
    {!! Html::label('Nama Kategori', 'name')->class('col-3') !!}
    <div class="col-9">
        {!! Html::text('name', old('name', $category->name ?? ''))->class('form-control')->attribute('required')->attribute('maxlength', 255) !!}
    </div>
</div>


<!-- Status Field -->
<div class="form-group row">
    {!! Html::label('Tampilkan', 'status')->class('col-3') !!}
    <div class="col-9">
        <label class="form-check-inline">
            {!! Html::radio('status')->value(1)->class('form-check-input')->checked(old('status', $category->status ?? 1) == 1) !!} Ya
        </label>
        <label class="form-check-inline">
            {!! Html::radio('status')->value(0)->class('form-check-input')->checked(old('status', $category->status ?? 1) == 0) !!} Tidak
        </label>
    </div>
</div>

{!! JsValidator::formRequest('App\Http\Requests\CreateCategoryRequest') !!}
