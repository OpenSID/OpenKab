@extends('layouts.index')

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/progressive-image/progressive-image.css') }}">
@endpush

@section('title', 'Data Statistik')

@section('content_header')
    <h1>Data Statistik {{ $judul }}</h1>
@stop

@section('content')
    <div class="row" id="tampilkan-statistik">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistik {{ $judul }}</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column" id="daftar-statistik">
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
                                href="#grafik-statistik" role="button" aria-expanded="false"
                                aria-controls="grafik-statistik">
                                <i class="fas fa-chart-bar"></i> Grafik
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button id="btn-pie" class="btn btn-sm btn-warning btn-block btn-sm" data-toggle="collapse"
                                href="#pie-statistik" role="button" aria-expanded="false" aria-controls="pie-statistik">
                                <i class="fas fa-chart-pie"></i> Chart
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <select class="form-control" id="tahun">

                            </select>

                        </div>
                        <div class="col-md-2">
                            <select class="form-control" id="bulan">
                                <option value=""></option>
                                <option value="1">Januari</option>
                                <option value="2">Februari</option>
                                <option value="3">Maret</option>
                                <option value="4">April</option>
                                <option value="5">Mei</option>
                                <option value="6">Juni</option>
                                <option value="7">Juli</option>
                                <option value="8">Agustus</option>
                                <option value="9">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">

                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div id="grafik-statistik" class="collapse">
                                <div class="chart" id="grafik">
                                    <canvas id="barChart"
                                        style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                                <hr style="margin-right: -20px; margin-left: -20px;">
                            </div>

                            <div id="pie-statistik" class="collapse">
                                <div class="chart" id="pie">
                                    <canvas id="donutChart"
                                        style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                </div>
                                <hr style="margin-right: -20px; margin-left: -20px;">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped cell-border" id="tabel-data">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th id="judul_kolom_nama" width="50%"></th>
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
        var kategori = `{{ strtolower($judul) }}`;

        $.ajax({
            url: `{{ url('api/v1/statistik/kategori-statistik') }}/?filter[id]=${kategori}`,
            method: 'get',
            success: function(response) {
                if (response.data.length == 0) {
                    $('#tampilkan-statistik').html(`
                            <div class="col-lg-12">
                                <div class="alert alert-warning">
                                    <h5><i class="icon fas fa-exclamation-triangle"></i> Perhatian!</h5>
                                    Tidak ada data bantuan yang tersedia untuk Desa ${nama_desa}.
                                </div>
                            </div>
                        `);
                }

                var daftarKategoriStatistik = response.data
                var html = ''

                daftarKategoriStatistik.forEach(function(item, index) {
                    var id = item.id;
                    var nama = item.nama;
                    var judul_kolom_nama = item.judul_kolom_nama;

                    if (index == 0) {
                        $('#judul_kolom_nama').html(judul_kolom_nama)
                        $('#cetak').data('url',
                            `{{ url('statistik/cetak') }}/${kategori}/${id}`);
                        statistik.ajax.url(
                            `{{ url('api/v1/statistik') }}/${kategori}/?filter[id]=${id}`).load();
                    }
                    html += `
                        <li class="nav-item pilih-kategori">
                            <a data-id="${id}" data-judul_kolom_nama="${judul_kolom_nama}" data-nama="${nama}" class="nav-link ${index == 0 ? 'active' : ''}">
                                <i class="fas fa-angle-right"></i> ${nama}
                            </a>
                        </li>
                    `
                });

                $('#daftar-statistik').html(html)
            }
        });

        $('.pilih-kategori > a.active').trigger('click');

        $('#daftar-statistik').on('mouseenter', '.pilih-kategori > a', function() {
            $(this).css('cursor', 'pointer')
        });

        $('#cetak').on('click', function() {
            window.open($(this).data('url'), '_blank');
        });

        $('#btn-grafik').on('click', function() {
            $("#pie-statistik").collapse('hide');
        });

        $('#btn-pie').on('click', function() {
            $("#grafik-statistik").collapse('hide')
        });

        $('#daftar-statistik').on('click', '.pilih-kategori > a', function() {
            var id = $(this).data('id')
            var judul_kolom_nama = $(this).data('judul_kolom_nama')

            $('.pilih-kategori > a').removeClass('active')
            $(this).addClass('active')
            $('#judul_kolom_nama').html(judul_kolom_nama)

            statistik.ajax.url(`{{ url('api/v1/statistik') }}/${kategori}/?filter[id]=${id}`).load();
            $('#cetak').data('url', `{{ url('statistik/cetak') }}/${kategori}/${id}`);
        });

        var statistik = $('#tabel-data').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: false,
            searching: false,
            paging: false,
            info: false,
            ajax: {
                url: `{{ url('api/v1/statistik/penduduk') }}/?filter[id]=rentang-umur`,
                method: 'get',
                data: function(row) {
                    return {
                        "filter[sasaran]": $("#sasaran").val(),
                        "filter[tahun]": $("#tahun").val(),
                    };
                },
                dataSrc: function(json) {
                    if (json.data.length > 0) {
                        data_grafik = [];
                        json.data.forEach(function(item, index) {
                            data_grafik.push(item.attributes)
                        })

                        grafikPie()

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
        });

        statistik.on('draw.dt', function() {
            var dataTable = $('#tabel-data').DataTable();
            var pageInfo = dataTable.page.info();
            var recordsTotal = dataTable.data().count();

            statistik.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                if ((recordsTotal - i) <= 3) {
                    cell.innerHTML = '';
                } else {
                    cell.innerHTML = i + 1 + pageInfo.start;
                }
            });
        });

        $(function() {
            $('#tahun').select2({
                minimumResultsForSearch: -1,
                allowClear: true,
                theme: "bootstrap",
                ajax: {
                    url: '{{ url('api/v1/statistik/penduduk/reftahunpenduduk') }}',
                    dataType: 'json',
                    processResults: function(data) {
                        if (data.success != true) {
                            return null
                        };

                        const element = new Array();
                        for (let index = 0; index < data.data.length; index++) {
                            element.push({
                                id: data.data[index].tahun,
                                text: data.data[index].tahun
                            });
                        }

                        return {
                            results: element
                        };
                    }
                },
                placeholder: "Pilih Tahun",
            });

            $('#bulan').select2({
                minimumResultsForSearch: -1,
                allowClear: true,
                theme: "bootstrap",

                placeholder: "Pilih Bulan",
            });
        });
    </script>
@endsection
