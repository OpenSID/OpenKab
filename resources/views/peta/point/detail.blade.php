@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Rincian Tipe Lokasi')

@section('content_header')
    <h1>Rincian Tipe Lokasi</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')
    @include('layouts.components.selec2_wilayah_referensi')

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-12">
                            <a href="{{ url('point/sub/'.$point->id ) }} ">
                                <button type="button" class="btn btn-primary btn-sm"><i class="far fa-plus-square"></i>
                                    Tambah</button>
                            </a>
                            <a href="#" title="Hapus Data" id="multiple-delete" class="disabled btn btn-social btn-danger btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-trash-o"></i> Hapus</a>
                            <a href="{{ route('point') }}" class="btn btn-social btn-info btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-arrow-circle-left"></i> Kembali Ke Daftar Data Tipe Lokasi</a>
                        </div>
                    </div>
                </div>
                <div class="card-header">
                    @include('peta.point.rincian')
                </div>
                <div class="card-body">
                    <div class="box-body">
                        <h5><b>Daftar Terdata</b></h5>
                        @include('peta.point.filter')
                        
                        <hr>
                        @include('peta.point.table')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script nonce="{{ csp_nonce() }}">
    document.addEventListener("DOMContentLoaded", function(event) {
        const header = @include('layouts.components.header_bearer_api_gabungan');
        @include('peta.point.js.data_subpoint')
        @include('peta.point.js.checkbox')
        @include('peta.point.js.multiple_delete')
        @include('peta.point.js.delete')


    });
    </script>
@endsection

