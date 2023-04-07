@extends('layouts.index')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/progressive-image/progressive-image.css') }}">
@endpush

@section('title', 'Data Statistik')

@section('content_header')
    <h1>Data Statistik RTM</h1>
@stop

@section('content')
    <div class="row" id="tampilkan-rtm">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar RTM</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column" id="daftar-rtm">
                        @foreach ($kategori_statistik as $key => $value)
                            <li class="nav-item rtm">
                                <a data-id="{{ $key }}" class="nav-link {{ $loop->first ? 'active' : '' }}">
                                    <i class="fas fa-angle-right"></i> {{ $value }}
                                </a>
                            </li>
                        @endforeach

                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-2">
                            <button id="cetak" type="button" class="btn btn-primary btn-block btn-sm" data-url=""><i
                                    class="fa fa-print"></i>
                                Cetak</button>
                        </div>
                        <div class="col-md-2">
                            <button id="btn-grafik" class="btn btn-sm btn-success btn-block btn-sm" data-toggle="collapse"
                                href="#grafik-rtm" role="button" aria-expanded="false" aria-controls="grafik-rtm">
                                <i class="fas fa-chart-bar"></i> Grafik
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button id="btn-pie" class="btn btn-sm btn-warning btn-block btn-sm" data-toggle="collapse"
                                href="#pie-rtm" role="button" aria-expanded="false" aria-controls="pie-rtm">
                                <i class="fas fa-chart-pie"></i> Chart
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="grafik-rtm" class="collapse">
                                <div class="chart">
                                    <canvas id="barChart"
                                        style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                                <hr style="margin-right: -20px; margin-left: -20px;">
                            </div>

                            <div id="pie-rtm" class="collapse">
                                <div class="chart">
                                    <div class="card-body">
                                        <canvas id="donutChart"
                                            style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                    </div>
                                </div>
                                <hr style="margin-right: -20px; margin-left: -20px;">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped cell-border" id="statistik-rtm">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th width="50%">Jenis Kelompok</th>
                                    <th colspan="2" class="dt-head-center">Jumlah</th>
                                    <th colspan="2" class="dt-head-center">Laki - laki</th>
                                    <th colspan="2" class="dt-head-center">Perempuan</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('assets/progressive-image/progressive-image.js') }}"></script>
@endpush

@section('js')
    <script>
        var data_grafik = [];
        var nama_desa = `{{ session('desa.nama_desa') }}`;

        $('#daftar-rtm').on('mouseenter', '.rtm > a', function() {
            $(this).css('cursor', 'pointer')
        })

        $('#cetak').on('click', function() {
            window.open($(this).data('url'), '_blank');
        });

        var statistik = $('#statistik-rtm').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: false,
            searching: false,
            paging: false,
            info: false,
            ajax: {
                url: `{{ url('api/v1/statistik/rtm') }}`,
                method: 'get',
                dataSrc: function(json) {
                    if (json.data.length > 0) {
                        data_grafik = [];
                        json.data.forEach(function(item, index) {
                            data_grafik.push(item.attributes)
                        })

                        if (json.data.length != $('#statistik-rtm').data('length')) {
                            $('#statistik-rtm').data('length', json.data.length)
                            grafik_pie()
                        }

                        return json.data;
                    }

                    return false;
                },
            },
            columns: [{
                data: null,
            }, {
                data: function(data) {
                    return data.attributes.nama;
                },
            }, {
                data: function(data) {
                    return data.attributes.jumlah
                },
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.attributes.persentase_jumlah;
                },
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.attributes.laki_laki
                },
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.attributes.persentase_laki_laki;
                },
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.attributes.perempuan
                },
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.attributes.persentase_perempuan;
                },
                className: 'dt-body-right',
            }]
        })

        statistik.on('draw.dt', function() {
            var PageInfo = $('#statistik-rtm').DataTable().page.info();
            statistik.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        $('#btn-grafik').on('click', function() {
            $("#pie-rtm").collapse('hide')
        })

        $('#btn-pie').on('click', function() {
            $("#grafik-rtm").collapse('hide')
        })

        $('#daftar-rtm').on('click', '.rtm > a', function() {
            var id = $(this).data('id')
            var sasaran = $(this).data('sasaran')

            $('.rtm > a').removeClass('active')
            $(this).addClass('active')

            statistik.ajax.url(`{{ url('api/v1/statistik/rtm') }}/?filter[id]=${id}`).load();
            $('#cetak').data('url', `{{ url('statistik/rtm/cetak') }}/${id}`);

            grafik_pie();
        })

        function grafik_pie() {
            tampilkan_grafik(data_grafik)
            tampilkan_chart(data_grafik)
        }

        function tampilkan_grafik(areaChartData) {
            var areaChartData = modifikasi_data_grafik(areaChartData);

            console.log(areaChartData);

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
        }

        function modifikasi_data_grafik(data) {
            var data_baru = []
            data.forEach(function(item, index) {
                if (index == 0 || index == 1) {
                    let color = 'rgba(' + Math.floor(Math.random() * 255) + ',' + Math.floor(Math
                        .random() * 255) + ',' + Math.floor(Math.random() * 255) + ', 1)'
                    let colorPoint = '#' + Math.floor(Math.random() * 16777215).toString(16);

                    data_baru.push({
                        label: item.nama,
                        backgroundColor: color,
                        borderColor: color,
                        pointRadius: false,
                        pointColor: color,
                        pointStrokeColor: colorPoint,
                        pointHighlightFill: colorPoint,
                        pointHighlightStroke: color,
                        data: [item.jumlah, 1]
                    })
                }
            })

            return {
                labels: ['BDT'],
                datasets: data_baru
            }
        }

        function tampilkan_chart(areaChartData) {
            var donutData = modifikasi_data_chart(areaChartData);
            var donutChartCanvas = $('#donutChart').get(0).getContext('2d')
            var donutOptions = {
                maintainAspectRatio: false,
                responsive: true,
            }
            new Chart(donutChartCanvas, {
                type: 'doughnut',
                data: donutData,
                options: donutOptions
            })
        }

        function modifikasi_data_chart(chart) {
            var labels = [];
            var data = [];
            var backgroundColor = [];

            chart.forEach(function(item, index) {
                if (index == 0 || index == 1) {
                    let color = '#' + Math.floor(Math.random() * 16777215).toString(16);

                    labels.push(item.nama)
                    data.push(item.jumlah)
                    backgroundColor.push(color)
                }
            })

            return {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: backgroundColor,
                }]
            }
        }

        $('.rtm > a.active').trigger('click')
    </script>
@endsection
