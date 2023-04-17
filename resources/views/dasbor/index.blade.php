@extends('layouts.index')

@push('css')
@endpush

@section('title', 'Dasbor')

@section('content_header')
    <h1>Dasbor</h1>
@stop

@section('content')
    <x-adminlte-callout theme="warning">
        Selamat datang di Dasbor Utama
        {{ config('app.namaAplikasi') . ' ' . config('app.sebutanKab') . ' ' . config('app.namaKab') }}.
    </x-adminlte-callout>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            $('.progressive replace').progressiveImage();
        });
    </script>
@endpush
