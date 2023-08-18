@extends('layouts.index')

@section('title', 'Ubah Pengguna')

@section('content_header')
    <h1>Ubah Profil Pengguna</h1>
@stop

@section('content')
    @include('partials.flash_message')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <div class="card-header">
                        <a href="{{ route('password.change') }}" class="btn btn-danger btn-sm"><i
                            class="fas fa-lock"></i></i>&ensp;Ganti Password</a>
                    </div>
                    <form action="{{ route('profile.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="{{ $user->id }}">
                        @csrf
                        @method('PUT')
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-3 col-md-4">
                                    @include('user.foto')
                                </div>
                                <div class="col-lg-9 col-md-8">
                                    @include('user.form')
                                </div>
                            </div>
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
