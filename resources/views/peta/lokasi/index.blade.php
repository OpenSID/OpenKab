@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Lokasi')

@section('content_header')
    <h1>Data Lokasi</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')
    
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                @include('peta.lokasi.filter')
                
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                @include('peta.lokasi.table')

                        
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('css')
   <style nonce="{{ csp_nonce() }}" >
        .select2-results__option[aria-disabled=true] {
            display: none;
        }
    </style>
@endpush
@section('js')
    <script nonce="{{ csp_nonce() }}"  >
        document.addEventListener("DOMContentLoaded", function(event) {

                @include('peta.lokasi.js.data')
                @include('peta.lokasi.js.filter')

        });
    </script>
@endsection
