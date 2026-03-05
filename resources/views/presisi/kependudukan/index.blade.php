@extends('layouts.presisi.index')

@section('content_header')
@stop

@section('content')
    @include('presisi.partials.head')

    <div class="row">
        <div class="col-md-12">

            <div class="card rounded-0 border-0 shadow-none">
                @include('presisi.wilayah.summary')
            </div>
        </div>

        @include('presisi.wilayah.filter')
        

        <div class="col-12 wow fadeInUp" data-wow-delay="0.3s">
            <div class="info-box shadow-none rounded-0">
                <div class="info-box-content">
                    <div class="row">
                        <div class="col-md-3 @if ($id) d-none @endif">
                            <div class="card card-primary rounded-0 elevation-0 border">
                                <!-- <div class="card-header rounded-0">
                                            <h3 class="card-title">Statistik Penduduk</h3>

                                        </div> -->
                                @include('presisi.kependudukan.kategori')


                            </div>
                        </div>
                        <div class="@if ($id) col-md-12 @else col-md-9 @endif">
                            <div class="card card-primary card-outline rounded-0 elevation-0 border">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-1" style="display: none">
                                            <a class="btn btn-sm btn-secondary" data-toggle="collapse"
                                                href="#collapse-filter" role="button" aria-expanded="true"
                                                aria-controls="collapse-filter">
                                                <i class="fas fa-filter"></i>
                                            </a>
                                        </div>
                                        <div class="col-md-2">
                                            <button id="btn-grafik" class="btn btn-sm btn-success btn-block btn-sm"
                                                data-bs-toggle="collapse" href="#grafik-statistik" role="button"
                                                aria-expanded="false" aria-controls="grafik-statistik" disabled>
                                                <i class="fas fa-chart-bar"></i> Grafik
                                            </button>
                                        </div>
                                        <div class="col-md-2">
                                            <button id="btn-pie" class="btn btn-sm btn-warning btn-block btn-sm"
                                                data-bs-toggle="collapse" href="#pie-statistik" role="button"
                                                aria-expanded="false" aria-controls="pie-statistik">
                                                <i class="fas fa-chart-pie"></i> Chart
                                            </button>
                                        </div>
                                    </div>


                                </div>

                                <div class="card-body p-0">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div id="collapse-filter" class="collapse ">
                                                <div class="row m-0">
                                                    <div class=" col-6">
                                                        <div class="form-group">
                                                            <label>Kecamatan</label>
                                                            <select class="form-control " name="search_kecamatan"> </select>
                                                        </div>

                                                    </div>

                                                    <div class=" col-6">
                                                        <div class="form-group">
                                                            <label>{{ config('app.sebutanDesa') }}</label>
                                                            <select class="form-control " name="search_desa"> </select>
                                                        </div>

                                                    </div>



                                                </div>

                                                <hr class="mt-0">
                                            </div>
                                        </div>
                                    </div>


                                    <div class="chart" id="grafik" style="height:100%; min-height: 500px;">

                                    </div>

                                    <div class="chart" id="pie"
                                        style="height:100%; min-height: 500px; display: none">

                                    </div>
                                    
                                    <div class="table-responsive mailbox-messages" id="statistik_block">
                                        <table class="table table-hover table-striped" id="statistik">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th class="judul">Rentang Umur</th>
                                                    <th class="text-center">Jumlah</th>
                                                    <th class="text-center">Laki - laki</th>
                                                    <th class="text-center">Perempuan</th>

                                                </tr>
                                            </thead>

                                        </table>

                                    </div>

                                </div>


                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
@include('presisi.kependudukan.js')
