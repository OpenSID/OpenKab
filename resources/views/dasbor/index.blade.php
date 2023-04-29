@extends('layouts.index')

@section('plugins.chart', true)

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
        <a href="{{ url('penduduk') }}" class="unlink  col-12 col-sm-6 col-md-3">
            <x-adminlte-info-box id="penduduk" title="Penduduk" text="L : 2999 | P : 1999" icon="fas fa-lg fa-user"
                icon-theme="blue" />
        </a>

        <a href="{{ url('keluarga') }}" class="unlink  col-12 col-sm-6 col-md-3">
            <x-adminlte-info-box id="keluarga" title="Keluarga" text="2991" icon="fas fa-lg fa-users"
                icon-theme="red" />
        </a>

        <a href="{{ url('rtm') }}" class="unlink  col-12 col-sm-6 col-md-3">
            <x-adminlte-info-box id="rtm" title="RTM" text="221" icon="fas fa-lg fa-home" icon-theme="green" />
        </a>

        <a href="{{ url('bantuan') }}" class="unlink  col-12 col-sm-6 col-md-3">
            <x-adminlte-info-box id="bantuan" title="Bantuan" text="22" icon="fas fa-lg fa-handshake"
                icon-theme="yellow" />
        </a>
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
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-3">
                            <a class="btn btn-sm btn-secondary" data-toggle="collapse" href="#collapse-filter" role="button"
                               aria-expanded="false" aria-controls="collapse-filter">
                                <i class="fas fa-filter"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="collapse-filter" class="collapse">
                                <div class="row">
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Kelurahan</label>
                                            <select class="select2 form-control-sm" id="nama_desa" name="nama_desa"
                                                    data-placeholder="Nama Kelurahan" style="width: 100%;">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Tahun</label>
                                            <select class="select2 form-control-sm" id="tahun" name="tahun"
                                                    data-placeholder="Semua Tahun" style="width: 100%;">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Bulan</label>
                                            <select class="select2 form-control-sm" id="bulan" name="bulan"
                                                    data-placeholder="Semua Bulan" style="width: 100%;">
                                                <option value="01"></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="form-group">
                                            <label>Kategori</label>
                                            <select class="select2 form-control-sm" id="id_kategori" name="id_kategori"
                                                    data-placeholder="Semua Kategori" style="width: 100%;">
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="btn-group btn-group-sm btn-block">
                                                    <button type="button" id="reset" class="btn btn-secondary"><span
                                                            class="fas fa-ban"></span></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="btn-group btn-group-sm btn-block">
                                                    <button type="button" id="filter" class="btn btn-primary"><span
                                                            class="fas fa-search"></span></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr class="mt-0">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped" id="berita">
                            <thead>
                            <tr>
                                <th>No</th>
                                <th>Kelurahan</th>
                                <th>Jumlah Artikel Perkelurahan</th>
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
    <script>
        $(document).ready(function() {
            $.ajax({
                url: `{{ url('api/v1/dasbor') }}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    res = response.data;
                    dataGrafik = res.grafik_penduduk;
                    dataBerita = res.statistik_berita;
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
            rgb_l = randColorRGB();
            rgb_p = randColorRGB();
            hex_l = randColorHex();
            hex_p = randColorHex();

            var areaChartData = {
                labels: dataGrafik.kategori,
                datasets: [{
                        label: 'Perempuan',
                        backgroundColor: rgb_p,
                        borderColor: rgb_p,
                        pointRadius: false,
                        pointColor: hex_p,
                        pointStrokeColor: rgb_p,
                        pointHighlightFill: hex_p,
                        pointHighlightStroke: rgb_p,
                        data: dataGrafik.perempuan
                    },
                    {
                        label: 'Laki-laki',
                        backgroundColor: rgb_l,
                        borderColor: rgb_l,
                        pointRadius: false,
                        pointColor: rgb_l,
                        pointStrokeColor: hex_l,
                        pointHighlightFill: hex_l,
                        pointHighlightStroke: rgb_l,
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
        }
    </script>
    <script>
        var berita = $('#berita').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            searchPanes: {
                viewTotal: false,
                columns: [0]
            },
            ajax: {
                url: `{{ url('api/v1/dasbor') }}`,
                method: 'get',
                data: function(response) {
                    res = response.data;
                    row = res.statistik_berita;
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[search]": row.search.value,
                        "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]
                            ?.name,
                        "filter[nama_desa]": $("#nama_desa").val(),
                        "filter[tahun]": $("#tahun").val(),
                        "filter[bulan]": $("#bulan").val(),
                        "filter[id_kategori]": $("#id_kategori").val(),
                    };
                },
                dataSrc: function(json) {
                    json.recordsTotal = json.meta.pagination.total
                    json.recordsFiltered = json.meta.pagination.total

                    return json.data
                },
            },
            columnDefs: [{
                targets: '_all',
                className: 'text-nowrap',
            },
                {
                    targets: [0, 1, 2],
                    orderable: false,
                    searchable: false,
                },
            ],
            columns: [{
                data: null,
            },
                {
                    data: "attributes.nama_desa",
                    name: "nama_desa"
                },
                {
                    data: "attributes.jumlah",
                    name: "jumlah",
                    className: 'text-center'
                },
            ],
            order: [
                [1, 'asc']
            ]
        })

        berita.on('draw.dt', function() {
            var PageInfo = $('#berita').DataTable().page.info();
            berita.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        $('#id_kategori').select2({
            minimumResultsForSearch: -1,
            ajax: {
                url: '{{ url('api/v1/artikel') }}/id_kategori/',
                dataType: 'json',
                processResults: function(response) {
                    return {
                        results: response.data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.kategori
                            }
                        })
                    };
                }
            },
        });

        $('#tahun').select2({
            minimumResultsForSearch: -1,
            ajax: {
                url: '{{ url('api/v1/artikel') }}/tahun/',
                dataType: 'json',
                processResults: function(data) {
                    if (data.data.tahun_awal == null) {
                        return null
                    };
                    const element = new Array();

                    for (let index = data.data.tahun_awal; index <= data.data.tahun_akhir; index++) {
                        element.push({
                            id: index,
                            text: index
                        });
                    }

                    return {
                        results: element
                    };
                }
            },
        });

        $('#nama_desa').select2({
            minimumResultsForSearch: -1,
            ajax: {
                url: '{{ url('api/v1/artikel') }}/nama_desa/',
                dataType: 'json',
                processResults: function(response) {
                    return {
                        results: response.data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.kategori
                            }
                        })
                    };
                }
            },
        });

        $('#filter').on('click', function(e) {
            berita.draw();
        });

        $(document).on('click', '#reset', function(e) {
            e.preventDefault();
            $('#nama_desa').val('').change();
            $('#tahun').val('').change();
            $('#bulan').val('').change();
            $('#id_kategori').val('').change();

            berita.ajax.reload();
        });
    </script>
@endpush
