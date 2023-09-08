@extends('layouts.index')

@section('content_header')
    <h1>Edit Halaman</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')

    <div class="row">
        <div class="col-lg-12">
            @include('adminlte-templates::common.errors')
            <div class="card card-outline card-primary">
                <div class="card-header">
                <a href="{{ route('pages.index') }}" class="btn btn-primary btn-sm"><i
                        class="fas fa-arrow-circle-left"></i></i>&ensp;Kembali ke Daftar Halaman</a>
            </div>
            {!! Form::model($page, ['route' => ['pages.update', $page->id], 'method' => 'patch',  'enctype' => 'multipart/form-data']) !!}

            <div class="card-body">
                <div>
                    @include('pages.fields')
                </div>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
</div>
@endsection
