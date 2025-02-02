@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Tipe Lokasi')

@section('content_header')
    <h1>Data Tipe Lokasi</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-3">
                            <a class="btn btn-sm btn-secondary" data-toggle="collapse" href="#collapse-filter" role="button"
                                aria-expanded="false" aria-controls="collapse-filter">
                                <i class="fas fa-filter"></i>
                            </a>
                            <a href="{{ url('point/form') }} ">
                                <button type="button" class="btn btn-primary btn-sm"><i class="far fa-plus-square"></i>
                                    Tambah</button>
                            </a>
                            <a href="#" title="Hapus Data" id="multiple-delete" class="disabled btn btn-social btn-danger btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-trash-o"></i> Hapus</a>

                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @include('peta.point.filter')
                    @include('peta.point.table')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script nonce="{{ csp_nonce() }}"  >
    document.addEventListener("DOMContentLoaded", function(event) {
        @include('peta.point.js.data_point')
        @include('peta.point.js.data_status')
        @include('peta.point.js.filter')
        @include('peta.point.js.delete')
        @include('peta.point.js.checkbox')
        @include('peta.point.js.multiple_delete')

    })

    
    </script>
@endsection
