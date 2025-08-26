<!-- name Field -->
<div class="form-group row">
    {!! Html::label('name', 'Nama Kategori')->class('col-3') !!}
    <div class="col-9">
        {!! Html::text('name')->class('form-control')->attribute('required')->attribute('maxlength', 255) !!}
    </div>
</div>


<!-- Status Field -->
<div class="form-group row">
    {!! Html::label('status', 'Tampilkan')->class('col-3') !!}
    <div class="col-9">
        <label class="form-check-inline">
            {!! Html::radio('status', 1, $category?->status == 1 ? 1 : null)->class('form-check-input') !!} Ya
        </label>
        <label class="form-check-inline">
            {!! Html::radio('status', 0, $category?->status == 0 ? 0 : null)->class('form-check-input') !!} Tidak
        </label>
    </div>
</div>

{!! JsValidator::formRequest('App\Http\Requests\CreateCategoryRequest') !!}
