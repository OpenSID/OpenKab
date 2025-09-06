<!-- Title Field -->
<div class="form-group row">
    <div class="col-3">
        {!! Html::label('Judul', 'title') !!}
    </div>
    <div class="col-9">
        {!! Html::text('title', old('title', $slide->title ?? ''))->class('form-control')->attribute('required')->attribute('maxlength', 255) !!}
    </div>
</div>


<!-- Url Field -->
<div class="form-group row">
    <div class="col-3">
        {!! Html::label('Tautan', 'url') !!}
    </div>
    <div class="col-9">
        {!! Html::text('url', old('url', $slide->url ?? ''))->class('form-control')->attribute('maxlength', 255) !!}
    </div>
</div>

<!-- Description Field -->
<div class="form-group row">
    <div class="col-3">
        {!! Html::label('Keterangan', 'description') !!}
    </div>
    <div class="col-9">
        {!! Html::textarea('description', old('description', $slide->description ?? ''))->class('form-control')->attribute('rows', 4)->attribute('maxlength', 65535) !!}
    </div>
</div>


<!-- Status Field -->
<div class="form-group row">
    {!! Html::label('Status', 'state')->class('col-3') !!}
    <div class="col-9">
        <label class="form-check-inline">
            {!! Html::radio('state')->value(1)->class('form-check-input')->checked(old('state', $slide->state ?? 0) == 1) !!} Aktif
        </label>
        <label class="form-check-inline">
            {!! Html::radio('state')->value(0)->class('form-check-input')->checked(old('state', $slide->state ?? 0) == 0) !!} Non Aktif
        </label>
    </div>
</div>

<!-- Thumbnail Field -->
<div class="form-group row">
    {!! Html::label('Gambar', 'foto')->class('col-3') !!}
    <div class="col-9">
        <div class="col-6">
            @include('slides.foto')
        </div>
    </div>
</div>


{!! JsValidator::formRequest(App\Http\Requests\CreateSlideRequest::class) !!}
