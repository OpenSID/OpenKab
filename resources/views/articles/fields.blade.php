<div class="row">
    <!-- Debug: Count Categories = {{ count($categories) }} -->
    <div class="col-9">

        <!-- Title Field -->
        <div class="form-group">
            {!! Html::label('Judul Artikel', 'title') !!}
            {!! Html::text('title')->class('form-control')->required()->maxlength(255)->value(old('title', $article->title ?? null)) !!}
        </div>

        <!-- Content Field -->
        <div class="form-group col-sm-12 col-lg-12">
            {!! Html::label('Isi Artikel', 'content') !!}
            {!! Html::textarea('content')->class('form-control editor')->required()->maxlength(65535)->value(old('content', $article->content ?? null)) !!}
        </div>

    </div>
    <div class="col-3">
        <!-- thumbnail Field -->
        <div class="form-group">
            @include('articles.foto')
        </div>
        <!-- Category Id Field -->
        <div class="form-group">
            {!! Html::label('Kategori', 'category_id') !!}
            @if(count($categories) == 0)
                {!! Html::select('category_id', $categories)->class('form-control select2')->required()->value(old('category_id', $article->category_id ?? null))->attribute('disabled', 'disabled') !!}
                <small class="form-text text-danger">
                    Kategori artikel belum tersedia. <a href="{{ route('categories.create') }}">Buat kategori artikel sekarang</a>.
                </small>
            @else
                {!! Html::select('category_id', $categories)->class('form-control select2')->required()->value(old('category_id', $article->category_id ?? null)) !!}
            @endif
        </div>

        <!-- Category Id Field -->
        <div class="form-group">
            {!! Html::label('Status', 'state') !!}
            {!! Html::select('state', $stateItem)->class('form-control select2')->required()->value(old('state', $article->state ?? null)) !!}
        </div>

        <!-- Published At Field -->
        <div class="form-group ">
            {!! Html::label('Tanggal Terbit', 'published_at') !!}
            {!! Html::text('published_at')->class('form-control datepicker')->id('published_at')->value(old('published_at', $article?->local_published_at)) !!}
        </div>

        <div>
            {!! Html::button('<i class="fas fa-times"></i> Batal')->type('reset')->class('btn btn-danger btn-sm') !!}
            {!! Html::submit('<i class="fas fa-save"></i> Simpan')->class('btn btn-primary btn-sm') !!}
        </div>
    </div>
</div>

@include('partials.asset_datepicker')
@include('partials.asset_tinymce')
{!! JsValidator::formRequest(App\Http\Requests\CreateArticleRequest::class) !!}
