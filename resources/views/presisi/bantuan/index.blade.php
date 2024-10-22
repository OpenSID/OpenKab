@extends('layouts.presisi.index')

@section('content_header')
@stop

@section('content')
@include('presisi.partials.head')

    <div class="row">
        <div class="col-12 wow fadeInUp" data-wow-delay="0.3s">
            <div class="info-box shadow-none rounded-0">
                <div class="info-box-content">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card card-primary rounded-0 elevation-0 border">
                                <div class="card-header rounded-0">
                                    <h3 class="card-title">Statistik Bantuan</h3>

                                </div>
                                <div class="card-body p-0 ">
                                    <ul class="nav nav-pills flex-column" id="nav-statistik">
                                    <li class="nav-item active">
                                        <a href="javascript:;" class="nav-link rounded-0"
                                            data-key="penduduk" data-name="Penerima Bantuan Penduduk">
                                            <i class="fas fa-inbox"></i> Penerima Bantuan Penduduk
                                        </a>
                                    </li>
                                    <li class="nav-item active">
                                        <a href="javascript:;" class="nav-link rounded-0"
                                            data-key="keluarga" data-name="Penerima Bantuan Keluarga">
                                            <i class="fas fa-inbox"></i> Penerima Bantuan Keluarga
                                        </a>
                                    </li>
                                    @foreach ($statistik as $key => $sub)
                                        <li class="nav-item active">
                                            <a href="javascript:;" class="nav-link rounded-0"
                                                data-key="{{ $sub->slug }}" data-name="{{ $sub->nama }}">
                                                <i class="fas fa-inbox"></i> {{ $sub->nama }}
                                            </a>
                                        </li>
                                    @endforeach



                                    </ul>
                                </div>

                            </div>
                        </div>
                        <div class="col-md-9">
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
                                            <button id="btn-tabel" class="btn btn-sm btn-info btn-block btn-sm "
                                                data-bs-toggle="collapse" href="#Tabel" role="button"
                                                aria-expanded="false" aria-controls="grafik-Tabel" disabled>
                                                <i class="fas fa-chart-bar"></i> Tabel
                                            </button>
                                        </div>
                                        <div class="col-md-2">
                                            <button id="btn-grafik" class="btn btn-sm btn-success btn-block btn-sm"
                                                data-bs-toggle="collapse" href="#grafik-statistik" role="button"
                                                aria-expanded="false" aria-controls="grafik-statistik">
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
                                                            <label>Desa</label>
                                                            <select class="form-control " name="search_desa"> </select>
                                                        </div>

                                                    </div>



                                                </div>

                                                <hr class="mt-0">
                                            </div>
                                        </div>
                                    </div>


                                    <div class="chart" id="grafik" style="height: 500px; display: none">

                                    </div>

                                    <div class="chart" id="pie" style="height: 500px; display: none">

                                    </div>

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
@push('js')
    <script>
        $(function() {
            var statistik = [];
            var data_grafik = [];
            let exclude_chart = ['JUMLAH', 'BELUM MENGISI', 'TOTAL']

            $('#nav-statistik li a:first').addClass('active');
            $('#nav-statistik li').click(function(e) {
                e.preventDefault();
                $('#nav-statistik').find('li a.active').removeClass('active');
                $(this).find('a').addClass('active');

                $('#statistik thead').find('.judul').html($(this).find('a').data('name'))
                table.ajax.reload()
            });

            $('#btn-tabel').click(function() {
                $(this).prop('disabled', true);
                $('#btn-grafik').prop('disabled', false);
                $('#btn-pie').prop('disabled', false);

                $('#grafik').hide();
                $('#pie').hide()
                $('#statistik').show()
            })

            $('#btn-grafik').click(function() {
                $(this).prop('disabled', true);
                $('#btn-tabel').prop('disabled', false);
                $('#btn-pie').prop('disabled', false);

                $('#grafik').show();
                $('#pie').hide()
                $('#statistik').hide()
            })

            $('#btn-pie').click(function() {
                $(this).prop('disabled', true);
                $('#btn-tabel').prop('disabled', false);
                $('#btn-grafik').prop('disabled', false);

                $('#grafik').hide();
                $('#pie').show()
                $('#statistik').hide()
            })


            var table = $('#statistik').DataTable({
                "processing": true,
                "serverSide": true,
                'pageLength': -1,
                "ordering": false,
                paging: false,
                searching: false,
                info: false,

                'order': [
                    [0, 'desc']
                ],

                "ajax": {
                    "url": "{{ url('api/v1/statistik-web/bantuan') }}",
                    "type": "get",
                    "data": function(d) {
                        var nav = $('#nav-statistik').find('li a.active')
                        d['filter[id]'] = nav.data('key');
                        // d.config_desa = $('#position').val();
                    },
                    dataSrc: function(json) {
                        if (json.data.length > 0) {
                            data_grafik = [];
                            json.data.forEach(function(item, index) {
                                if (!exclude_chart.includes(item.attributes.nama)) {
                                    data_grafik.push(item.attributes)
                                }

                            });
                            grafikPie();

                        }
                        return json.data
                    },

                    error: function(xhr, status, error) {
                        // Handle the error callback here
                        console.error(error);
                    }
                },
                columns: [{
                        data: null,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'attributes.nama',
                        sortable: false,
                        searchable: false,

                    },

                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {

                            return `<td class="text-center"><span class="mr-4">${data.attributes.jumlah}  </span>  ${data.attributes
                                .persentase_jumlah}</td>`;
                        }
                    },

                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return `<td class="text-center"><span class="mr-4">${data.attributes.laki_laki}  </span>  ${data.attributes
                                .persentase_laki_laki}</td>`;
                        }
                    },

                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return `<td class="text-center"><span class="mr-4">${data.attributes?.perempuan}  </span>  ${data.attributes
                                .persentase_perempuan}</td>`;
                        }
                    },


                ],
            });

            function grafikPie() {
                $('#barChart').remove();
                $('#donutChart').remove();

                var data = modifikasiData(data_grafik);
                $('#grafik').append('<canvas id="barChart"></canvas>');
                $('#pie').append('<canvas id="donutChart"></canvas>');
                tampilGrafik(data[0]);
                tampilPie(data[1]);
            }

            function tampilGrafik(areaChartData) {
                var barChartCanvas = $('#barChart').get(0).getContext('2d')
                var barChartData = $.extend(true, {}, areaChartData)
                var temp0 = areaChartData.datasets[0] ?? []
                var temp1 = areaChartData.datasets[1] ?? []
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

            function tampilPie(donutData) {
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

            function modifikasiData(data) {
                var dataBaruGrafik = []
                var labelsPie = [];
                var dataPie = [];
                var backgroundColorPie = [];

                data.forEach(function(item, index) {
                    let color = randColorRGB();
                    let colorPoint = randColorHex();

                    dataBaruGrafik.push({
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

                    labelsPie.push(item.nama)
                    dataPie.push(item.jumlah)
                    backgroundColorPie.push(color)
                })

                return [{
                        labels: ['Data'],
                        datasets: dataBaruGrafik
                    },
                    {
                        labels: labelsPie,
                        datasets: [{
                            data: dataPie,
                            backgroundColor: backgroundColorPie,
                        }]
                    }
                ]
            }
        });
    </script>

    <script nonce="{{ csp_nonce() }}" type="text/javascript">
        document.addEventListener("DOMContentLoaded", function(event) {
            "use strict";
            $.get('{{ url('index.php/api/v1/data-website') }}', {}, function(result) {
                let category = result.data.categoriesItems
                let listDesa = result.data.listDesa
                let listKecamatan = result.data.listKecamatan

                for (let index in category) {
                    $(`.kategori-item .jumlah-${index}-elm`).text(category[index]['value'])
                };
                let _optionKecamatan = []
                let _optionDesa = []
                for (let item in listKecamatan) {
                    _optionKecamatan.push(`<option>${item}</option>`)
                }

                for (let item in listDesa) {
                    _optionDesa.push(`<optgroup label='${item}'>`)
                    for (let desa in listDesa[item]) {
                        _optionDesa.push(`<option value='${desa}'>${listDesa[item][desa]}</option>`)
                    }
                    _optionDesa.push(`</optgroup>`)
                    _optionKecamatan.push(`<option>${item}</option>`)
                }

                $('select[name=search_kecamatan]').append(_optionKecamatan.join(''))
                $('select[name=search_desa]').append(_optionDesa.join(''))
            }, 'json')
        });
    </script>
@endpush
