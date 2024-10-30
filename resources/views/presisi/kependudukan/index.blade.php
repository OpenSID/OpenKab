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

        <div class="col-12 wow fadeInUp" data-wow-delay="0.3s">
            <div class="info-box shadow-none rounded-0">
                <div class="info-box-content">
                    <div class="row">
                        <div class="col-md-3 @if($id) d-none @endif" >
                            <div class="card card-primary rounded-0 elevation-0 border">
                                <!-- <div class="card-header rounded-0">
                                    <h3 class="card-title">Statistik Penduduk</h3>

                                </div> -->
                                @include('presisi.kependudukan.kategori')


                            </div>
                        </div>
                        <div class="@if($id) col-md-12 @else col-md-9 @endif">
                            <div class="card card-primary card-outline rounded-0 elevation-0 border">
                            
                                

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
                                                            <label>Desa</label>
                                                            <select class="form-control " name="search_desa"> </select>
                                                        </div>

                                                    </div>



                                                </div>

                                                <hr class="mt-0">
                                            </div>
                                        </div>
                                    </div>


                                    @if(!$id)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="chart" id="pie" style="height: 500px;"></div><br><br>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="chart" id="grafik" style="height: 500px;"></div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="chart" id="grafik" style="height: 500px;"></div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="chart" id="pie" style="height: 500px;"></div>
                                        </div>
                                    </div>
                                    @endif

                                    <div class="table-responsive mailbox-messages">
                                        <table class="table table-hover table-striped" id="statistik">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th class="judul">Kelompok</th>
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
