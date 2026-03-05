@extends('layouts.index')

@section('plugins.chart', true)

@section('title', 'Dasbor')


@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="card rounded-0 border-0 shadow-none col-12">
            <div class="card-body">
                @include('dasbor.filter')
                @include('dasbor.summary')
            </div>
        </div>
        {{-- @include('dasbor.statistik_penduduk') --}}
        <div class="card rounded-0 border-0 shadow-none col-12">
            <div class="card-body">
                @include('dasbor.peta')
            </div>
        </div>
        <div class="card rounded-0 border-0 shadow-none col-12">
            <div class="card-body">
                @include('dasbor.tabel_penduduk')
            </div>
        </div>
    </div>

@endsection
@push('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            $('li#kabupaten').remove();
            $('li#kecamatan').remove();
            $('li#desa').remove();

            $('#bt_filter').click(function() {
                $('#summary_block').trigger('change');
                //$('#statistik_penduduk_block').trigger('change');
                $('#tabel_penduduk_block').trigger('change');
                $('#map').trigger('change');
            });
        })
    </script>
@endpush
@push('css')
    <style nonce="{{ csp_nonce() }}">
        #barChart {
            min-height: 300px;
            height: 300px;
            max-height: 300px;
            max-width: 100%;
            display: block;
            width: 379px;
        }
    </style>
@endpush
