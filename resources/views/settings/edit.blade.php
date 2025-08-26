@extends('layouts.index')

@section('content_header')
    <h1>Edit Setting</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')

    <div class="row">
        <div class="col-lg-12">
            @include('common.errors')
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('settings.index') }}" class="btn btn-primary btn-sm"><i
                            class="fas fa-arrow-circle-left"></i></i>&ensp;Kembali ke Daftar Setting</a>
                </div>
                {!! Html::form('PUT', route('settings.update', $setting->id))->bind($setting)->open() !!}

                <div class="card-body">
                    <div>
                        @include('settings.fields')
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
