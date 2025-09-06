<div class="row">
    <div class="col-9">
        <!-- Title Field -->
        <div class="form-group">
            {!! Html::label('Judul', 'title') !!}
            {!! Html::text('title', old('title', $page->title ?? ''))->class('form-control')->attribute('required')->attribute('maxlength', 200) !!}
        </div>

        <!-- Content Field -->
        <div class="form-group col-sm-12 col-lg-12">
            {!! Html::label('Isi', 'content') !!}
            {!! Html::textarea('content', old('content', $page->content ?? ''))->class('form-control editor')->attribute('required')->attribute('maxlength', 65535) !!}
        </div>
    </div>
    <div class="col-3">
        <!-- thumbnail Field -->
        <div class="form-group">
            @include('pages.foto')
        </div>

        <!-- Published At Field -->
        <div class="form-group ">
            {!! Html::label('Tanggal Posting', 'published_at') !!}
            {!! Html::text('published_at', $page?->local_published_at ?? null)->class('form-control datepicker')->id('published_at')->attribute('required') !!}
        </div>

        <!-- State Field -->
        <div class="form-group">
            {!! Html::label('Status', 'state') !!}
            {!! Html::select('state', $stateItem)->class('form-control select2')->attribute('required') !!}
        </div>

        <div>
            {!! Html::button('<i class="fas fa-times"></i> Batal')->type('reset')->class('btn btn-danger btn-sm') !!}
            {!! Html::button('<i class="fas fa-save"></i> Simpan')->type('submit')->class('btn btn-primary btn-sm') !!}
        </div>
    </div>
</div>

@include('partials.asset_datepicker')
@include('partials.asset_tinymce')
{!! JsValidator::formRequest('App\Http\Requests\CreatePageRequest') !!}
