@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Suplemen')

@section('content_header')
    <h1>{{ $action }} Data Suplemen</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @if (Session::has('success'))
        <div class="alert alert-success">
            {{ Session::get('success') }}
        </div>
    @elseif (Session::has('error'))
        <div class="alert alert-danger">
            {{ Session::get('error') }}
        </div>
    @endif
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a href="{{ route('suplemen') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-circle-left"></i>&ensp;Kembali ke Daftar Data Suplemen
                    </a>
                </div>
                <form method="POST" class="form-horizontal" id="formSuplemen">
                    @csrf
                    <input type="hidden" name="sumber" value="OpenKab">
                    <input type="hidden" name="form_isian" id="form_isian">
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="sasaran">Sasaran Data</label>
                            <div class="col-sm-9">
                                <select class="form-control form-control-sm required" required name="sasaran">
                                    <option value="">Pilih Sasaran</option>
                                    @foreach ($list_sasaran as $key => $sasaran)
                                        @if (in_array($key, ['1', '2']))
                                            <option value="{{ $key }}">{{ $sasaran }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="nama">Nama Data Suplemen</label>
                            <div class="col-sm-9">
                                <input class="form-control form-control-sm required" maxlength="100" placeholder="Nama Data Suplemen" type="text" name="nama" value="">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="keterangan">Keterangan</label>
                            <div class="col-sm-9">
                                <textarea name="keterangan" class="form-control form-control-sm" maxlength="300" placeholder="Keterangan" rows="3" style="resize:none;"></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label" for="status">Status</label>
                            <div class="col-sm-9">
                                <select class="form-control form-control-sm required" required name="status">
                                    <option value="">Pilih Status</option>
                                    <option value="1">Aktif</option>
                                    <option value="0">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            @include('suplemen.kode_isian')
                        </div>


                    </div>
                    <div class="card-footer">
                        @include('layouts.reset_form')
                        <button type="button" class="btn btn-secondary btn-sm pull-right" id="previewButton">
                            <i class="fa fa-eye"></i> Preview
                        </button>
                        <button type="submit" class="btn btn-info btn-sm pull-right">
                            <i class="fa fa-check"></i> Simpan
                        </button>
                    </div>                    
                </form>
            </div>
        </div>
    </div>
@include('suplemen.preview')
    

@endsection

@section('js')
@include('suplemen.kode_isian_js')
@include('suplemen.form_js')
@include('suplemen.preview_js')
@endsection
