<!-- Name Field -->
<div class="form-group row">
    <div class="col-12">
    {!! Form::text('text', null, ['class' => 'form-control item-menu', 'maxlength' => 255, 'placeholder' => 'Nama Menu']) !!}
    {!! Form::hidden('icon', 'fas fa-list', ['class' => 'item-menu']) !!}
    </div>
</div>

<!-- Asal url -->
<div class="form-group row">
    <div class="col-12">
        <label class="form-check-inline">
            {!! Form::radio('source', 1, 1, ['class' => 'form-check-input']) !!} Link
        </label>
        <label class="form-check-inline">
            {!! Form::radio('source', 2, null, ['class' => 'form-check-input']) !!} Halaman
        </label>
        <label class="form-check-inline">
            {!! Form::radio('source', 3, null, ['class' => 'form-check-input']) !!} Kategori
        </label>
    </div>
</div>

<!-- Url Field -->
<div class="form-group row">
    <div class="col-12">
    {!! Form::text('href', null, ['class' => 'form-control item-menu', 'maxlength' => 255, 'placeholder' => 'http://contoh.com']) !!}
    </div>
</div>
