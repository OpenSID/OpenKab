@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Infrastruktur')

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">Statistik Kondisi Transportasi</div>
                <div class="card-body">
                    <div class="chart" id="grafik">
                        <canvas id="kondisiChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Statistik Sanitasi</div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="sanitasiChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="float-left">Data Sarana dan Prasarana</div>
                </div>
                <div class="card-body">
                    <div id="infrastruktur"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')

@include('data_pokok.infrastruktur.data')
@include('data_pokok.infrastruktur.pie')
@include('data_pokok.infrastruktur.grafik')

@endsection
@include('data_pokok.infrastruktur.style')
