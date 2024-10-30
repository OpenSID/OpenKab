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
                        {{-- <div class="col-md-3">
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
                        </div> --}}
                        <div class="col-md-12">
                            <div class="card card-primary card-outline rounded-0 elevation-0 border">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="row">
                                                <p> Pilih Tahun:</p>
                                            </div>
                                            <div class="row">
                                                <select name="Filter Tahun" id="filter_tahun" required class="form-control" title="Pilih Tahun">
                                                    <option value="">All</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="row">
                                                <p> Pilih Kecamatan:</p>
                                            </div>
                                            <div class="row">
                                                <select name="Filter Kecamatan" id="filter_kecamatan" required class="form-control" title="Pilih Kecamatan">
                                                    <option value="">All</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="row">
                                                <p> Pilih Desa:</p>
                                            </div>
                                            <div class="row">
                                                <select name="Filter Desa" id="filter_desa" required class="form-control" title="Pilih Desa">
                                                    <option value="">All</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row justify-content-end pt-1">
                                        <div class="col-md-4 pull-right text-right">
                                            <button id="bt_clear_filter" class="btn btn-sm btn-danger pull-right" style="display:none;">HAPUS FILTER</button>
                                            <button id="bt_filter" class="btn btn-sm btn-secondary pull-right">TAMPILKAN</button>
                                        </div>
                                    </div>
                                    <div class="row @if($id) d-none @endif">
                                        <div class="col-md-2">
                                            <p> Pilih Program:</p>
                                        </div>
                                        <div class="col-md-4">
                                            <select name="Filter Program" id="filter_program" required class="form-control" title="Pilih Program">
                                                <option value="">All</option>
                                            </select>
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
                                    <br/>
                                    <h5 class="pl-2"> Tabel Penerima Bantuan</h5>
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
                                    <br/>
                                    <h5 class="pl-2"> Daftar Penerima Bantuan</h5>
                                    <div class="table-responsive mailbox-messages">
                                        <table class="table table-hover table-striped" id="daftar_penerima">
                                            <thead>
                                                <tr>
                                                    <th>Program</th>
                                                    <th>Nama Peserta</th>
                                                    <th>Alamat</th>
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
            let exclude_chart = ['JUMLAH', 'BELUM MENGISI', 'TOTAL'];

            $('#filter_tahun').select2({
                placeholder: "Pilih Tahun"
            });
            $('#filter_kecamatan').select2({
                placeholder: "Pilih Kecamatan"
            });
            $('#filter_desa').select2({
                placeholder: "Pilih Desa"
            });
            $('#filter_program').select2({
                placeholder: "Pilih Program"
            });

            GetListTahun();
            GetListKecamatan();
            GetListDesa();
            GetListProgram();


            $('#nav-statistik li a:first').addClass('active');
            $('#nav-statistik li').click(function(e) {
                e.preventDefault();
                $('#nav-statistik').find('li a.active').removeClass('active');
                $(this).find('a').addClass('active');

                $('#statistik thead').find('.judul').html($(this).find('a').data('name'))
                table.ajax.reload();
                table_penerima.ajax.reload();
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
                //$('#btn-tabel').prop('disabled', false);
                $('#btn-pie').prop('disabled', false);

                $('#grafik').show();
                $('#pie').hide()
                //$('#statistik').hide()
            })

            $('#btn-pie').click(function() {
                $(this).prop('disabled', true);
                //$('#btn-tabel').prop('disabled', false);
                $('#btn-grafik').prop('disabled', false);

                $('#grafik').hide();
                $('#pie').show()
               // $('#statistik').hide()
            })

            $('#filter_kecamatan').on("select2:select", function(e) {
                GetListDesa(this.value);
            });

            $('#filter_program').on("select2:select", function(e) {
                $('#statistik thead').find('.judul').html($("#filter_program").select2('data')[0].text);
                table.ajax.reload();
                table_penerima.ajax.reload();
            });

            $('#bt_clear_filter').click(function(){
                $("#filter_tahun").val("").trigger("change");
                $("#filter_kecamatan").val("").trigger("change");
                $("#filter_desa").val("").trigger("change");
                $('#filter_desa').empty().trigger("change");
                $('#bt_clear_filter').hide();
                table.ajax.reload();
                table_penerima.ajax.reload();
            });

            $('#bt_filter').click(function(){
                $('#bt_clear_filter').show();
                table.ajax.reload();
                table_penerima.ajax.reload();
            });


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
                        // var nav = $('#nav-statistik').find('li a.active')
                        // d['filter[id]'] = nav.data('key');
                        d['filter[id]'] =  $("#filter_program").val();
                        d['filter[tahun]'] = $("#filter_tahun").val();
                        d['filter[kecamatan]'] = $("#filter_kecamatan").val();
                        d['filter[desa]'] = $("#filter_desa").val();
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

            var table_penerima = $('#daftar_penerima').DataTable({
                "processing": true,
                "serverSide": false,
                'pageLength': 10,
                "ordering": true,
                paging: true,
                searching: true,
                info: false,

                'order': [
                    [0, 'asc']
                ],

                "ajax": {
                    "url": "{{ url('api/v1/statistik-web/get-list-penerima') }}",
                    "type": "get",
                    "data": function(d) {
                        d['filter[id]'] =  $("#filter_program").val();
                        d['filter[tahun]'] = $("#filter_tahun").val();
                        d['filter[kecamatan]'] = $("#filter_kecamatan").val();
                        d['filter[desa]'] = $("#filter_desa").val();
                    },
                    "dataSrc": function (d) {
                        return d
                    },
                    error: function(xhr, status, error) {
                        // Handle the error callback here
                        console.error(error);
                    }
                },
                columns: [
                    {
                        data: 'nama_program',
                        sortable: true,
                        searchable: true,

                    },
                    {
                    data: 'nama_penerima',
                        sortable: true,
                        searchable: true,

                    },
                    {
                    data: 'alamat_penerima',
                        sortable: true,
                        searchable: true,

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

        function GetListTahun() {
            $('#filter_tahun').empty().trigger("change");
            var optionEmpty = new Option("", "");
            $("#filter_tahun").append(optionEmpty);
            $.ajax({
                type: 'GET',
                url: "{{ url('api/v1/statistik-web/get-list-tahun') }}",
                dataType: 'json',
                success: function(data) {
                    for (var i = 0; i < data.length; i++) {
                        var newOption = new Option(data[i].year, data[i].year, true, true);
                        $("#filter_tahun").append(newOption);
                    }
                    $("#filter_tahun").val("");
                },
                error: function(jqXHR, textStatus, errorThrown) {

                }
            });
        }
        function GetListKecamatan() {
            $('#filter_kecamatan').empty().trigger("change");
            var optionEmpty = new Option("", "");
            $("#filter_kecamatan").append(optionEmpty);
            $.ajax({
                type: 'GET',
                url: "{{ url('api/v1/statistik-web/get-list-kecamatan') }}",
                dataType: 'json',
                success: function(data) {
                    for (var i = 0; i < data.length; i++) {
                        var newOption = new Option(data[i].nama_kecamatan, data[i].kode_kecamatan, true, true);
                        $("#filter_kecamatan").append(newOption);
                    }
                    $("#filter_kecamatan").val("").trigger("change");
                },
                error: function(jqXHR, textStatus, errorThrown) {

                }
            });
        }
        function GetListDesa($id) {
            $('#filter_desa').empty().trigger("change");
            if ($id != undefined && $id != null){
                var optionEmpty = new Option("", "");
                $("#filter_desa").append(optionEmpty);
                $.ajax({
                    type: 'GET',
                    url: "{{ url('api/v1/statistik-web/get-list-desa') }}" + "/" + $id,
                    dataType: 'json',
                    success: function(data) {
                        for (var i = 0; i < data.length; i++) {
                            var newOption = new Option(data[i].nama_desa, data[i].kode_desa, true, true);
                            $("#filter_desa").append(newOption);
                        }
                        $("#filter_desa").val("");
                    },
                    error: function(jqXHR, textStatus, errorThrown) {

                    }
                });
            }
        }

        @include('presisi.bantuan.list_program')

        

    </script>
@endpush
