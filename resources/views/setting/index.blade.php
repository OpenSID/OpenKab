@extends('layouts.index')

@section('title', 'Ubah Pengaturan Aplikasi')

@section('content_header')
    <h1>Ubah Pengaturan Aplikasi</h1>
@stop

@section('content')
    @include('partials.flash_message')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <form action="{{ route('setting-aplikasi.store') }}" method="POST">
                @csrf
                <!-- /.card-header -->
                    <div class="card-body">
                        @include('setting.form')
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="button" id="reset" class="btn btn-danger btn-sm"><i
                                class="fas fa-times"></i>&nbsp; Batal</button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i>&nbsp;
                            Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@include('partials.reset_form')
