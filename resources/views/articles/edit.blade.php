@extends('layouts.index')

@section('content_header')
    <h1>Edit Artikel</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')

    <div class="row">
        <div class="col-lg-12">
            @include('common.errors')
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('articles.index') }}" class="btn btn-primary btn-sm"><i
                            class="fas fa-arrow-circle-left"></i></i>&ensp;Kembali ke Daftar Artikel</a>
                </div>
                {!! Html::form('PUT', route('articles.update', $article->id))->attribute('enctype', 'multipart/form-data')->bind($article)->open() !!}

                <div class="card-body">
                    <div>
                        @include('articles.fields')
                    </div>
                </div>

                {!! Html::form()->close() !!}

            </div>
        </div>
    </div>
@endsection
