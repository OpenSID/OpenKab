@extends('layouts.presisi.index')

@section('content_header')
@stop
@section('content')
    @include('presisi.partials.head')

    <div class="row m-1">
        <div class="col-12">
            <div class="card rounded-0 border-0 shadow-none">
                @include('presisi.wilayah.summary')
                <div class="card-body">
                    @include('presisi.wilayah.filter')
                    @include('presisi.wilayah.peta')
                    @include('presisi.wilayah.data-wilayah')

                    @include('presisi.wilayah.tabel_penduduk')
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script nonce="{{ csp_nonce() }}" type="text/javascript">
        document.addEventListener("DOMContentLoaded", function(event) {
            "use strict";

            $('#bt_filter').click(function() {
                $('#summary_block').trigger('change');
                $('#tabel_penduduk_block').trigger('change');
                $('#summary_wilayah_block').trigger('change');
                $('#map').trigger('change');
            });
        });
    </script>
@endpush
