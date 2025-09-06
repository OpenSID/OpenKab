@extends('layouts.index')

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            @include('common.errors')
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('departments.index') }}" class="btn btn-primary btn-sm"><i
                            class="fas fa-arrow-circle-left"></i></i>&ensp;Kembali ke Daftar Department</a>
                </div>
                {!! Html::form('POST', route('departments.store'))->open() !!}

                <div class="card-body">

                    <div>
                        @include('departments.fields')
                    </div>

                </div>

                <div class="card-footer">
                    {!! Html::button('<i class="fas fa-times"></i> Batal')->type('reset')->class('btn btn-danger btn-sm') !!}
                    {!! Html::button('<i class="fas fa-save"></i> Simpan')->type('submit')->class('btn btn-primary btn-sm') !!}
                </div>

                {!! Html::form()->close() !!}

            </div>
        </div>
    </div>
@endsection
