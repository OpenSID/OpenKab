<!-- Title Field -->
<div class="form-group row">
    <div class="col-3">
        {!! Html::label('title', 'Judul') !!}
    </div>
    <div class="col-9">
        {!! Html::text('title', old('title', $slide->title ?? ''))->class('form-control')->attribute('required')->attribute('maxlength', 255) !!}
    </div>
</div>


<!-- Url Field -->
<div class="form-group row">
    <div class="col-3">
        {!! Html::label('url', 'Url') !!}
    </div>
    <div class="col-9">
        {!! Html::text('url', old('url', $slide->url ?? ''))->class('form-control')->attribute('maxlength', 255) !!}
    </div>
</div>

<!-- Description Field -->
<div class="form-group row">
    <div class="col-3">
        {!! Html::label('description', 'Keterangan') !!}
    </div>
    <div class="col-9">
        {!! Html::textarea('description', old('description', $slide->description ?? ''))->class('form-control')->attribute('rows', 4)->attribute('maxlength', 65535) !!}
    </div>
</div>


<!-- Status Field -->
<div class="form-group row">
    {!! Html::label('state', 'Status')->class('col-3') !!}
    <div class="col-9">
        <label class="form-check-inline">
            {!! Html::radio('state', 1)->class('form-check-input')->checked(old('state', $slide->state ?? 0) == 1) !!} Aktif
        </label>
        <label class="form-check-inline">
            {!! Html::radio('state', 0)->class('form-check-input')->checked(old('state', $slide->state ?? 0) == 0) !!} Non Aktif
        </label>
    </div>
</div>

<!-- Thumbnail Field -->
<div class="form-group row">
    {!! Html::label('foto', 'Gambar')->class('col-3') !!}
    <div class="col-9">
        <div class="col-6">
            @include('slides.foto')
        </div>
    </div>
</div>


{!! JsValidator::formRequest(App\Http\Requests\CreateSlideRequest::class) !!}
