<!-- Title Field -->
<div class="form-group row">
    <div class="col-3">
        {!! Form::label('title', 'Judul') !!}
    </div>
    <div class="col-9">
        {!! Form::text('title', null, ['class' => 'form-control', 'required', 'maxlength' => 255]) !!}
    </div>
</div>


<!-- Url Field -->
<div class="form-group row">
    <div class="col-3">
        {!! Form::label('url', 'Url') !!}
    </div>
    <div class="col-9">
        {!! Form::text('url', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
    </div>
</div>

<!-- Description Field -->
<div class="form-group row">
    <div class="col-3">
        {!! Form::label('description', 'Keterangan') !!}
    </div>
    <div class="col-9">
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 4, 'maxlength' => 65535])
        !!}
    </div>
</div>


<!-- Status Field -->
<div class="form-group row">
    {!! Form::label('state', 'Status', ['class' => 'col-3']) !!}
    <div class="col-9">
        <label class="form-check-inline">
            {!! Form::radio('state', 1, $slide?->state == 1 ? 1 : null, ['class' => 'form-check-input']) !!} Aktif
        </label>
        <label class="form-check-inline">
            {!! Form::radio('state', 0, $slide?->state == 0 ? 0 : null, ['class' => 'form-check-input']) !!} Non Aktif
        </label>
    </div>
</div>

<!-- Thumbnail Field -->
<div class="form-group row">
    {!! Form::label('foto', 'Gambar', ['class' => 'col-3']) !!}
    <div class="col-9">
        <div class="col-6">
            @include('slides.foto')
        </div>
    </div>
</div>


{!! JsValidator::formRequest(App\Http\Requests\CreateSlideRequest::class) !!}
