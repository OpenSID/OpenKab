@extends('layouts.presisi.index')

@section('content_header')
@stop

@section('content')
    @include('presisi.partials.head')
    <div class="row">
        <div class="col-md-12">
            <div class="card rounded-0 border-0 shadow-none">
                @include('presisi.summary')
            </div>
        </div>
        @include('presisi.kesehatan.filter-wilayah.view')
    </div>
    @include('presisi.kesehatan.widget')
    <div class="row">
        <div class="col-12 wow fadeInUp" data-wow-delay="0.3s">
            <div class="info-box shadow-none rounded-0">
                <div class="info-box-content">
                    @include('presisi.kesehatan.chart_stunting_umur')
                    @include('presisi.kesehatan.chart_stunting_posyandu')
                    @include('presisi.kesehatan.scorecard')
                </div>
            </div>
        </div>

    </div>
@endsection
@push('js')
    <script nonce="{{ csp_nonce() }}">
        $('#cari').click(function() {
            let kuartal = $('#kuartal option:selected').val();
            let tahun = $('#tahun option:selected').val();
            let posyandu = $('#id option:selected').val();
            let kabupaten = $("#filter_kabupaten").val() ?? null;
            let kecamatan = $("#filter_kecamatan").val() ?? null;
            let desa = $("#filter_desa").val() ?? null;
            window.location.href = "{{ url('presisi/kesehatan/') }}/" + kuartal + "/" +
                tahun + "/" + posyandu + "/" + kabupaten + "/" + kecamatan + "/" + desa;
        });

        @include('presisi.kesehatan.filter-wilayah.kabupaten')
        @include('presisi.kesehatan.filter-wilayah.kecamatan')
        @include('presisi.kesehatan.filter-wilayah.desa')
        @include('presisi.kesehatan.filter-wilayah.button')
    </script>

    @include('presisi.summary_js')
@endpush
