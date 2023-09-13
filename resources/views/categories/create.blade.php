@extends('layouts.index')

@section('content_header')
    <h1>Tambah Kategori</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            @include('adminlte-templates::common.errors')
            <div class="card card-outline card-primary">
                <div class="card-header">
                <a href="{{ route('categories.index') }}" class="btn btn-primary btn-sm"><i
                        class="fas fa-arrow-circle-left"></i></i>&ensp;Kembali ke Daftar Kategori</a>
            </div>
            {!! Form::open(['route' => 'categories.store']) !!}

            <div class="card-body">

                <div>
                    @include('categories.fields')
                </div>

            </div>

            <div class="card-footer">
                {!! Form::button('<i class="fas fa-times"></i> Batal', ['type' => 'reset', 'class' => 'btn btn-danger btn-sm'] )  !!}
                {!! Form::button('<i class="fas fa-save"></i> Simpan', ['type' => 'submit', 'class' => 'btn btn-primary btn-sm'] )  !!}
            </div>

            {!! Form::close() !!}

        </div>
    </div>
</div>
@endsection
