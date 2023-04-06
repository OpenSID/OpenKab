@extends('layouts.index')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/progressive-image/progressive-image.css') }}">
@endpush

@section('title', 'Data Statistik')

@section('content_header')
    <h1>Data Statistik Bantuan</h1>
@stop

@section('content')
    <div class="row" id="tampilkan-bantuan">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Bantuan</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column" id="daftar-bantuan">
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
                                href="#grafik-bantuan" role="button" aria-expanded="false" aria-controls="grafik-bantuan">
                                <i class="fas fa-chart-bar"></i> Grafik
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button id="btn-pie" class="btn btn-sm btn-warning btn-block btn-sm" data-toggle="collapse"
                                href="#pie-bantuan" role="button" aria-expanded="false" aria-controls="pie-bantuan">
                                <i class="fas fa-chart-pie"></i> Chart
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="grafik-bantuan" class="collapse">
                                <div class="chart">
                                    <canvas id="barChart"
                                        style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                                <hr style="margin-right: -20px; margin-left: -20px;">
                            </div>

                            <div id="pie-bantuan" class="collapse">
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
                        <table class="table table-striped cell-border" id="statistik-bantuan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th id="judul_sasaran" width="50%"></th>
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

        $.ajax({
            url: `{{ url('api/v1/statistik/bantuan') }}`,
            method: 'get',
            success: function(response) {
                if (response.data.length == 0) {
                    $('#tampilkan-bantuan').html(`
                        <div class="col-lg-12">
                            <div class="alert alert-warning">
                                <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                                Tidak ada data bantuan yang tersedia untuk Desa ${nama_desa}.
                            </div>
                        </div>
                    `)
                }

                var daftar_bantuan = response.data
                var html = ''

                daftar_bantuan.forEach(function(item, index) {
                    html += `
                        <li class="nav-item bantuan">
                            <a data-id="${item.id}" class="nav-link ${index == 0 ? 'active' : ''}">
                                <i class="fas fa-angle-right"></i> ${item.attributes.nama}
                            </a>
                        </li>
                    `
                })

                $('#daftar-bantuan').html(html)
            }
        });

        $('#daftar-bantuan').on('mouseenter', '.bantuan > a', function() {
            $(this).css('cursor', 'pointer')
        })

        $('#cetak').on('click', function() {
            window.open($(this).data('url'), '_blank');
        });

        var statistik = $('#statistik-bantuan').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: false,
            ajax: {
                url: `{{ url('api/v1/statistik/bantuan/grafik') }}`,
                method: 'get',
                dataSrc: function(json) {
                    if (json.data.length > 0) {
                        json.statistik = json.data[0].attributes.sasaran
                        json.recordsTotal = json.meta.pagination.total
                        json.recordsFiltered = json.meta.pagination.total

                        $('#judul_sasaran').html('Sasaran ' + json.data[0].attributes.nama_sasaran);
                        $('#cetak').data('url',
                            `{{ url('statistik/bantuan/cetak') }}/${json.data[0].id}`);

                        data_grafik.push(json.data[0].attributes)

                        if (data_grafik.length == 1) {
                            grafik_pie();
                        }

                        return json.data[0].attributes.statistik
                    }

                    return false;
                },
            },
            columns: [{
                data: null,
            }, {
                data: "nama"
            }, {
                data: "jumlah",
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.persentase_jumlah.toFixed(2) + '%';
                },
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.laki_laki
                },
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.persentase_laki_laki.toFixed(2) + '%';
                },
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.perempuan
                },
                className: 'dt-body-right',
            }, {
                data: function(data) {
                    return data.persentase_perempuan.toFixed(2) + '%';
                },
                className: 'dt-body-right',
            }]
        })

        statistik.on('draw.dt', function() {
            var PageInfo = $('#statistik-bantuan').DataTable().page.info();
            statistik.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        $('#btn-grafik').on('click', function() {
            $("#pie-bantuan").collapse('hide')
        })

        $('#btn-pie').on('click', function() {
            $("#grafik-bantuan").collapse('hide')
        })

        $(document).on('click', '.bantuan > a', function(e) {
            e.preventDefault()

            var id = $(this).data('id')

            $('.bantuan > a').removeClass('active')
            $(this).addClass('active')

            $('#cetak').data('url', `{{ url('statistik/bantuan/cetak') }}/${id}`);

            grafik_pie();

            statistik.ajax.url(`{{ url('api/v1/statistik/bantuan/grafik') }}/?filter[id]=${id}`).load()
        })

        function grafik_pie() {
            tampilkan_grafik(data_grafik[0])
            tampilkan_chart(data_grafik[0])
        }

        function tampilkan_grafik(areaChartData) {
            var areaChartData = modifikasi_data_grafik(areaChartData);

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

            data.statistik.forEach(function(item, index) {
                if (index == 0 || index == 1) {
                    let color = 'rgba(' + Math.floor(Math.random() * 255) + ',' + Math.floor(Math
                        .random() * 255) + ',' + Math.floor(Math.random() * 255) + ', 1)'

                    data_baru.push({
                        label: item.nama,
                        backgroundColor: color,
                        borderColor: color,
                        pointRadius: false,
                        pointColor: color,
                        pointStrokeColor: '#c1c7d1',
                        pointHighlightFill: '#fff',
                        pointHighlightStroke: color,
                        data: [item.jumlah, 1]
                    })
                }
            })

            return {
                labels: [data.nama],
                datasets: data_baru
            }
        }

        function tampilkan_chart(areaChartData) {
            var donutData = modifikasi_data_chart(areaChartData);
            console.log(donutData);
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

            chart.statistik.forEach(function(item, index) {
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
    </script>
@endsection
