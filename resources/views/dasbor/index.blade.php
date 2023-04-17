@extends('layouts.index')

@push('css')
@endpush

@section('title', 'Dasbor')

@section('content_header')
    <h1>Dasbor</h1>
@stop

@section('content')
    <x-adminlte-callout theme="warning">
        Selamat datang <b>{{ Auth::user()->username }}</b> di Dasbor Utama
        <b>{{ config('app.namaAplikasi') . ' ' . config('app.sebutanKab') . ' ' . config('app.namaKab') }}</b>.
    </x-adminlte-callout>

    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <x-adminlte-info-box id="penduduk" title="Penduduk" text="L : 2999 | P : 1999" icon="fas fa-lg fa-user"
                icon-theme="blue" />
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <x-adminlte-info-box id="keluarga" title="Keluarga" text="2991" icon="fas fa-lg fa-users" icon-theme="red" />
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <x-adminlte-info-box id="rtm" title="RTM" text="221" icon="fas fa-lg fa-home" icon-theme="green" />
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <x-adminlte-info-box id="bantuan" title="Bantuan" text="22" icon="fas fa-lg fa-handshake"
                icon-theme="yellow" />
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            $.ajax({
                url: `{{ url('api/v1/dasbor') }}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    jumlah = response.data;
                    $('#penduduk').find('.info-box-number').text('L : ' + jumlah
                        .jumlah_penduduk_laki_laki +
                        ' | P : ' + jumlah.jumlah_penduduk_perempuan);
                    $('#keluarga').find('.info-box-number').text(jumlah.jumlah_keluarga);
                    $('#rtm').find('.info-box-number').text(jumlah.jumlah_rtm);
                    $('#bantuan').find('.info-box-number').text(jumlah.jumlah_bantuan);
                }
            });
        });
    </script>
@endpush
