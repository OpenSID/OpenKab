@extends('layouts.index')

@push('css')
@endpush

@section('title', 'Dasbor')

@section('content_header')
    <h1>Dasbor</h1>
@stop

@section('content')
    <x-adminlte-callout theme="warning">
        Selamat datang <b>{{ Auth::user()->username ?? '' }}</b> di Dasbor Utama
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
        <div class="col-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        Statistik Penduduk
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="barChart"
                            style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%; display: block; width: 379px;"
                            width="758" height="500" class="chartjs-render-monitor"></canvas>
                    </div>

                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Stacked Bar Chart</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart">
                                <canvas id="stackedBarChart"
                                    style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Line Chart</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="remove">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart">
                                <canvas id="lineChart"
                                    style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
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
                            res = response.data;
                            dataGrafik = res.grafik_penduduk;
                            $('#penduduk').find('.info-box-number').text('L : ' + res
                                .jumlah_penduduk_laki_laki +
                                ' | P : ' + res.jumlah_penduduk_perempuan);
                            $('#keluarga').find('.info-box-number').text(res.jumlah_keluarga);
                            $('#rtm').find('.info-box-number').text(res.jumlah_rtm);
                            $('#bantuan').find('.info-box-number').text(res.jumlah_bantuan);

                            grafik(dataGrafik);
                        }
                    });
                });

                function grafik(dataGrafik) {
                    // console.log(dataGrafik.kategori);
                    // console.log(dataGrafik.laki_laki);
                    // console.log(perempuan);

                    var areaChartData = {
                        labels: dataGrafik.kategori,
                        datasets: [{
                                label: 'Perempuan',
                                backgroundColor: 'rgba(60,141,188,0.9)',
                                borderColor: 'rgba(60,141,188,0.8)',
                                pointRadius: false,
                                pointColor: '#3b8bba',
                                pointStrokeColor: 'rgba(60,141,188,1)',
                                pointHighlightFill: '#fff',
                                pointHighlightStroke: 'rgba(60,141,188,1)',
                                data: dataGrafik.perempuan
                            },
                            {
                                label: 'Laki-laki',
                                backgroundColor: 'rgba(210, 214, 222, 1)',
                                borderColor: 'rgba(210, 214, 222, 1)',
                                pointRadius: false,
                                pointColor: 'rgba(210, 214, 222, 1)',
                                pointStrokeColor: '#c1c7d1',
                                pointHighlightFill: '#fff',
                                pointHighlightStroke: 'rgba(220,220,220,1)',
                                data: dataGrafik.laki_laki
                            },
                        ]
                    }

                    var areaChartOptions = {
                        maintainAspectRatio: false,
                        responsive: true,
                        legend: {
                            display: false
                        },
                        scales: {
                            xAxes: [{
                                gridLines: {
                                    display: false,
                                }
                            }],
                            yAxes: [{
                                gridLines: {
                                    display: false,
                                }
                            }]
                        }
                    }

                    //-------------
                    //- LINE CHART -
                    //--------------
                    var lineChartCanvas = $('#lineChart').get(0).getContext('2d')
                    var lineChartOptions = $.extend(true, {}, areaChartOptions)
                    var lineChartData = $.extend(true, {}, areaChartData)
                    lineChartData.datasets[0].fill = false;
                    lineChartData.datasets[1].fill = false;
                    lineChartOptions.datasetFill = false

                    var lineChart = new Chart(lineChartCanvas, {
                        type: 'line',
                        data: lineChartData,
                        options: lineChartOptions
                    })


                    var areaChartData = {
                        labels: dataGrafik.kategori,
                        datasets: [{
                                label: 'Perempuan',
                                backgroundColor: 'rgba(60,141,188,0.9)',
                                borderColor: 'rgba(60,141,188,0.8)',
                                pointRadius: false,
                                pointColor: '#3b8bba',
                                pointStrokeColor: 'rgba(60,141,188,1)',
                                pointHighlightFill: '#fff',
                                pointHighlightStroke: 'rgba(60,141,188,1)',
                                data: dataGrafik.perempuan
                            },
                            {
                                label: 'Laki-laki',
                                backgroundColor: 'rgba(210, 214, 222, 1)',
                                borderColor: 'rgba(210, 214, 222, 1)',
                                pointRadius: false,
                                pointColor: 'rgba(210, 214, 222, 1)',
                                pointStrokeColor: '#c1c7d1',
                                pointHighlightFill: '#fff',
                                pointHighlightStroke: 'rgba(220,220,220,1)',
                                data: dataGrafik.laki_laki
                            },
                        ]
                    }

                    var barChartCanvas = $('#barChart').get(0).getContext('2d')
                    var barChartData = $.extend(true, {}, areaChartData)
                    var temp0 = areaChartData.datasets[0]
                    var temp1 = areaChartData.datasets[1]
                    barChartData.datasets[0] = temp1
                    barChartData.datasets[1] = temp0

                    var barChartOptions = {
                        responsive: true,
                        maintainAspectRatio: false,
                        datasetFill: false
                    }

                    new Chart(barChartCanvas, {
                        type: 'bar',
                        data: barChartData,
                        options: barChartOptions
                    })

                    //---------------------
                    //- STACKED BAR CHART -
                    //---------------------
                    var stackedBarChartCanvas = $('#stackedBarChart').get(0).getContext('2d')
                    var stackedBarChartData = $.extend(true, {}, barChartData)

                    var stackedBarChartOptions = {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxes: [{
                                stacked: true,
                            }],
                            yAxes: [{
                                stacked: true
                            }]
                        }
                    }

                    new Chart(stackedBarChartCanvas, {
                        type: 'bar',
                        data: stackedBarChartData,
                        options: stackedBarChartOptions
                    })
                }
            </script>
        @endpush
