@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Suplemen')

@section('content_header')
    <h1>Data Suplemen</h1>
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
                            <a href="{{ route('profile.kependudukan.suplemen.create') }} ">
                                <button type="button" class="btn btn-primary btn-sm"><i class="far fa-plus-square"></i>
                                    Tambah</button>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @include('suplemen.filter')
                    @include('suplemen.table')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script nonce="{{ csp_nonce() }}"  >
    document.addEventListener("DOMContentLoaded", function(event) {
        @include('suplemen.js.data_suplemen')
        @include('suplemen.js.data_sasaran')
        @include('suplemen.js.data_status')
        @include('suplemen.js.filter')
        @include('suplemen.js.delete')
    })

    
    </script>
@endsection
