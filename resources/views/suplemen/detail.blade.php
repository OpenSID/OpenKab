@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Daftar Terdata Suplemen')

@section('content_header')
    <h1>Daftar Terdata Suplemen</h1>
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
                            @include('layouts.components.tombol_cetak_unduh', [
                                'cetak' => "suplemen/daftar/{$id}/cetak",
                                'unduh' => "suplemen/daftar/{$id}/unduh",
                            ])
                            @include('layouts.components.tombol_ekspor', [
                                'ekspor' => "suplemen/ekspor/{$id}",
                            ])
                            <a href="#" title="Hapus Data" id="multiple-delete" class="disabled btn btn-social btn-danger btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-trash-o"></i> Hapus</a>
                            <a href="{{ route('suplemen') }}" class="btn btn-social btn-info btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-arrow-circle-left"></i> Kembali Ke Daftar Data Suplemen</a>
                        </div>
                    </div>
                </div>
                <div class="card-header">
                    @include('suplemen.rincian')
                </div>
                <div class="card-body">
                    <div class="box-body">
                        <h5><b>Daftar Terdata</b></h5>
                        @include('suplemen.filter_terdata')
                        
                        <hr>
                        @include('suplemen.table_terdata')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script nonce="{{ csp_nonce() }}">
    document.addEventListener("DOMContentLoaded", function(event) {
        @include('suplemen.js.data_suplemen_terdata')
        @include('suplemen.js.checkbox')
        @include('suplemen.js.multiple_delete')
        @include('suplemen.js.data_suplemen_terdata_detail')

    });
    </script>
@endsection

