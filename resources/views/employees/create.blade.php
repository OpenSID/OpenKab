@extends('layouts.index')

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            @include('adminlte-templates::common.errors')
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('employees.index') }}" class="btn btn-primary btn-sm"><i
                            class="fas fa-arrow-circle-left"></i></i>&ensp;Kembali ke Daftar Employee</a>
                </div>
                {!! Form::open(['route' => 'employees.store', 'enctype' => 'multipart/form-data']) !!}

                <div class="card-body">

                    <div class="row">
                        <div class="col-lg-3 col-md-4">
                            @include('employees.foto')
                        </div>
                        <div class="col-lg-9 col-md-8">
                            @include('employees.fields')
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    {!! Form::button('<i class="fas fa-times"></i> Batal', ['type' => 'reset', 'class' => 'btn
                    btn-danger btn-sm'] ) !!}
                    {!! Form::button('<i class="fas fa-save"></i> Simpan', ['type' => 'submit', 'class' => 'btn
                    btn-primary btn-sm'] ) !!}
                </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div>
@endsection
