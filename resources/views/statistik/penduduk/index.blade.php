@extends('layouts.index')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/progressive-image/progressive-image.css') }}">
@endpush

@section('title', 'Data Statistik')

@section('content_header')
    <h1>Data Statistik Penduduk</h1>
@stop

@section('content')
    <div class="row" id="tampilkan-penduduk">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Penduduk</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column" id="daftar-penduduk">
                        @foreach ($kategori_statistik as $key => $value)
                            <li class="nav-item penduduk">
                                <a data-slug="{{ $key }}" class="nav-link {{ $loop->first ? 'active' : '' }}">
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
                                href="#grafik-penduduk" role="button" aria-expanded="false"
                                aria-controls="grafik-penduduk">
                                <i class="fas fa-chart-bar"></i> Grafik
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button id="btn-pie" class="btn btn-sm btn-warning btn-block btn-sm" data-toggle="collapse"
                                href="#pie-penduduk" role="button" aria-expanded="false" aria-controls="pie-penduduk">
                                <i class="fas fa-chart-pie"></i> Chart
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="grafik-penduduk" class="collapse">
                                <div class="chart">
                                    <canvas id="barChart"
                                        style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                                <hr style="margin-right: -20px; margin-left: -20px;">
                            </div>

                            <div id="pie-penduduk" class="collapse">
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
                        <table class="table table-striped cell-border" id="statistik-penduduk">
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
    @include('statistik.chart')
    <script>
        var data_grafik = [];
        var nama_desa = `{{ session('desa.nama_desa') }}`;

        $('#daftar-penduduk').on('mouseenter', '.penduduk > a', function() {
            $(this).css('cursor', 'pointer')
        })

        $('#cetak').on('click', function() {
            window.open($(this).data('url'), '_blank');
        });

        var statistik = $('#statistik-penduduk').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: false,
            searching: false,
            paging: false,
            info: false,
            ajax: {
                url: `{{ url('api/v1/statistik/penduduk?filter[slug]=umur-rentang') }}`,
                method: 'get',
                dataSrc: function(json) {
                    if (json.data.length > 0) {
                        data_grafik = [];
                        json.data.forEach(function(item, index) {
                            data_grafik.push(item.attributes)
                        })

                        if (json.data.length != $('#statistik-penduduk').data('length')) {
                            $('#statistik-penduduk').data('length', json.data.length)
                            grafikPie()
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
            var PageInfo = $('#statistik-penduduk').DataTable().page.info();
            statistik.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        $('#btn-grafik').on('click', function() {
            $("#pie-penduduk").collapse('hide')
        })

        $('#btn-pie').on('click', function() {
            $("#grafik-penduduk").collapse('hide')
        })

        $('#daftar-penduduk').on('click', '.penduduk > a', function() {
            var slug = $(this).data('slug')

            $('.penduduk > a').removeClass('active')
            $(this).addClass('active')

            statistik.ajax.url(`{{ url('api/v1/statistik/penduduk') }}/?filter[slug]=${slug}`).load();
            $('#cetak').data('url', `{{ url('statistik/penduduk/cetak') }}/${slug}`);

            grafikPie();
        })

        $('.penduduk > a.active').trigger('click')
    </script>
@endsection
