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
                        <div class="col-md-3 @if($id) d-none @endif">
                            <div class="card card-primary rounded-0 elevation-0 border">
                                <div class="card-header rounded-0">
                                    <h3 class="card-title">Statistik RTM</h3>

                                </div>
                                @include('presisi.rtm.kategori')


                            </div>
                        </div>
                        <div class="@if($id) col-md-12 @else col-md-9 @endif">
                            <div class="card card-primary card-outline rounded-0 elevation-0 border">
                            @include('presisi.rtm.tab')

                                <div class="card-body p-0">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="chart" id="grafik" style="height: 500px;"></div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="chart" id="pie" style="height: 500px; display:none"></div>
                                        </div>
                                    </div>

                                    

                                    <div class="table-responsive mailbox-messages" id="statistik_block">
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
@include('presisi.rtm.js')
