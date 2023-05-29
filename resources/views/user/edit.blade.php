@extends('layouts.index')

@section('title', 'Ubah Pengguna')

@section('content_header')
    <h1>Ubah Pengguna</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <div class="card-header">
                        <a href="{{ route('users.index') }}" class="btn btn-primary btn-sm"><i
                                class="fas fa-arrow-circle-left"></i></i>&ensp;Kembali ke Daftar Pengguna</a>
                    </div>
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        <input type="hidden" name="id" value="{{ $user->id }}">
                        @csrf
                        @method('PUT')
                        <!-- /.card-header -->
                        <div class="card-body">
                            @include('user.form')
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <button type="button" id="reset" class="btn btn-default btn-sm">&nbsp; Reset</button>
                            <button type="submit" class="btn btn-primary btn-sm">&nbsp; Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('partials.reset_form')
