<div class="row">
    <div class="col-9">

        <!-- Title Field -->
        <div class="form-group">
            {!! Form::label('title', 'Judul Artikel') !!}
            {!! Form::text('title', null, ['class' => 'form-control', 'required', 'maxlength' => 255, 'maxlength' => 255]) !!}
        </div>

        <!-- Content Field -->
        <div class="form-group col-sm-12 col-lg-12">
            {!! Form::label('content', 'Isi Artikel') !!}
            {!! Form::textarea('content', null, ['class' => 'form-control editor', 'required', 'maxlength' => 65535, 'maxlength' => 65535]) !!}
        </div>

    </div>
    <div class="col-3">
        <!-- thumbnail Field -->
        <div class="form-group">
            @include('articles.foto')
        </div>
        <!-- Category Id Field -->
        <div class="form-group">
            {!! Form::label('category_id', 'Kategori') !!}
            {!! Form::select('category_id', $categories, null, ['class' => 'form-control select2', 'required']) !!}
        </div>

        <!-- Category Id Field -->
        <div class="form-group">
            {!! Form::label('state', 'Status') !!}
            {!! Form::select('state', $stateItem, null, ['class' => 'form-control select2', 'required']) !!}
        </div>

        <!-- Published At Field -->
        <div class="form-group ">
            {!! Form::label('published_at', 'Tanggal Terbit') !!}
            {!! Form::text('published_at', $article?->local_published_at , ['class' => 'form-control datepicker','id'=>'published_at']) !!}
        </div>

        <div>
            {!! Form::button('<i class="fas fa-times"></i> Batal', ['type' => 'reset', 'class' => 'btn btn-danger btn-sm'] )  !!}
            {!! Form::button('<i class="fas fa-save"></i> Simpan', ['type' => 'submit', 'class' => 'btn btn-primary btn-sm'] )  !!}
        </div>
    </div>
</div>

@include('partials.asset_datepicker')
@include('partials.asset_tinymce')
