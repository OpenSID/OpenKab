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
                        <div class="col-md-12">
                            <div class="card card-primary card-outline rounded-0 elevation-0 border-0">
                                <div class="card-header bg-primary rounded-0">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="row">
                                                <select name="Filter Tahun" id="filter_tahun" required class="form-control"
                                                    title="Tahun">
                                                    <option value="">All</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="row">
                                                <select name="Filter Kabupaten" id="filter_kabupaten" required
                                                    class="form-control" title="Pilih Kabupaten">
                                                    <option value="">All</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="row">
                                                <select name="Filter Kecamatan" id="filter_kecamatan" required
                                                    class="form-control" title="Pilih Kecamatan">
                                                    <option value="">All</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="row">
                                                <select name="Filter Desa" id="filter_desa" required class="form-control"
                                                    title="Pilih Desa">
                                                    <option value="">All</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="row">
                                                <table>
                                                    <tr>
                                                        <td>
                                                            <button id="bt_clear_filter"
                                                                class="btn btn-sm btn-danger pull-right wh-full">HAPUS
                                                                FILTER</button>
                                                        </td>
                                                        <td>
                                                            <button id="bt_filter"
                                                                class="btn btn-sm btn-primary btn-dark-primary wh-full">TAMPILKAN</button>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- <div class="row justify-content-end pt-1">
                                                        <div class="col-md-4 pull-right text-right">
                                                            <button id="bt_clear_filter" class="btn btn-sm btn-danger pull-right">HAPUS FILTER</button>
                                                            <button id="bt_filter" class="btn btn-sm btn-secondary pull-right">TAMPILKAN</button>
                                                        </div>
                                                    </div> -->
                                    <hr class="@if ($id) d-none @endif">
                                    <div class="row @if ($id) d-none @endif">
                                        <div class="col-md-2">
                                            <p> Pilih Program:</p>
                                        </div>
                                        <div class="col-md-3">
                                            <select name="Filter Program" id="filter_program" required class="form-control"
                                                title="Pilih Program">
                                                <option value="">All</option>
                                            </select>
                                        </div>
                                    </div>



                                </div>
                                <div class="card-header">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <button id="btn-grafik" class="btn btn-sm btn-success btn-block btn-sm"
                                                data-bs-toggle="collapse" href="#grafik-statistik" role="button"
                                                aria-expanded="false" aria-controls="grafik-statistik" disabled>
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
                                                    <div class=" col-4">
                                                        <div class="form-group">
                                                            <label>Kabupaten</label>
                                                            <select class="form-control " name="search_kabupaten"> </select>
                                                        </div>

                                                    </div>
                                                    <div class=" col-4">
                                                        <div class="form-group">
                                                            <label>Kecamatan</label>
                                                            <select class="form-control " name="search_kecamatan"> </select>
                                                        </div>

                                                    </div>

                                                    <div class=" col-4">
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


                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="chart" id="pie" style="height: 500px;display:none"></div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="chart" id="grafik" style="height: 500px;"></div>
                                        </div>
                                    </div>
                                    <br />
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
                                    <br />
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
    <script nonce="{{ csp_nonce() }}">
        $(function() {

            var statistik = [];
            var data_grafik = [];
            let exclude_chart = ['JUMLAH', 'BELUM MENGISI', 'TOTAL'];

            $('#filter_tahun').select2({
                placeholder: "Tahun"
            });
            $('#filter_kabupaten').select2({
                placeholder: "Pilih Kabupaten"
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
            GetListKabupaten();
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

            $('#filter_kabupaten').on("select2:select", function(e) {
                GetListKecamatan(this.value);
            });

            $('#filter_kecamatan').on("select2:select", function(e) {
                GetListDesa(this.value);
            });

            $('#filter_program').on("select2:select", function(e) {
                $('#statistik thead').find('.judul').html($("#filter_program").select2('data')[0].text);
                table.ajax.reload();
                table_penerima.ajax.reload();
            });

            $('#bt_clear_filter').click(function() {
                $("#filter_tahun").val("").trigger("change");
                $("#filter_kabupaten").val("").trigger("change");
                $("#filter_kecamatan").val("").trigger("change");
                $("#filter_desa").val("").trigger("change");
                $('#filter_desa').empty().trigger("change");
                $('#bt_clear_filter').hide();
                table.ajax.reload();
                table_penerima.ajax.reload();
            });

            $('#bt_filter').click(function() {
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
                    "url": "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/bantuan' }}",
                    "type": "get",
                    "data": function(d) {
                        // var nav = $('#nav-statistik').find('li a.active')
                        // d['filter[id]'] = nav.data('key');
                        d['filter[id]'] = $("#filter_program").val();
                        d['filter[tahun]'] = $("#filter_tahun").val();
                        d['filter[kabupaten]'] = $("#filter_kabupaten").val();
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
                    "url": "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-penerima' }}",
                    "type": "get",
                    "data": function(d) {
                        d['filter[id]'] = $("#filter_program").val();
                        d['filter[tahun]'] = $("#filter_tahun").val();
                        d['filter[kabupaten]'] = $("#filter_kabupaten").val();
                        d['filter[kecamatan]'] = $("#filter_kecamatan").val();
                        d['filter[desa]'] = $("#filter_desa").val();
                    },
                    "dataSrc": function(d) {
                        return d
                    },
                    error: function(xhr, status, error) {
                        // Handle the error callback here
                        console.error(error);
                    }
                },
                columns: [{
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

            const header = @include('layouts.components.header_bearer_api_gabungan');
            var urlDataWebsite = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/data-website' }}");

            $.ajax({
                url: urlDataWebsite.href,
                method: 'GET',
                dataType: 'json',
                headers: header,
                success: function(result) {
                    let category = result.data.categoriesItems;
                    let listDesa = result.data.listDesa;
                    let listKecamatan = result.data.listKecamatan;
                    let listKabupaten = result.data.listKabupaten;

                    for (let index in category) {
                        $(`.kategori-item .jumlah-${index}-elm`).text(category[index]['value']);
                    }

                    let _optionKabupaten = [];
                    let _optionKecamatan = [];
                    let _optionDesa = [];

                    for (let item in listKabupaten) {
                        _optionKabupaten.push(`<option>${item}</option>`);
                    }

                    for (let item in listKecamatan) {
                        _optionKecamatan.push(`<optgroup label='${item}'>`);
                        for (let kecamatan in listKecamatan[item]) {
                            _optionKecamatan.push(
                                `<option value='${kecamatan}'>${listKecamatan[item][kecamatan]}</option>`
                            );
                        }
                        _optionKecamatan.push(`</optgroup>`);
                        _optionKabupaten.push(`<option>${item}</option>`);
                    }

                    for (let item in listDesa) {
                        _optionDesa.push(`<optgroup label='${item}'>`);
                        for (let desa in listDesa[item]) {
                            _optionDesa.push(
                                `<option value='${desa}'>${listDesa[item][desa]}</option>`);
                        }
                        _optionDesa.push(`</optgroup>`);
                        _optionKecamatan.push(`<option>${item}</option>`);
                    }

                    $('select[name=search_kabupaten]').append(_optionKabupaten.join(''));
                    $('select[name=search_kecamatan]').append(_optionKecamatan.join(''));
                    $('select[name=search_desa]').append(_optionDesa.join(''));
                }
            });

        });

        $('#btn-grafik').click(function() {
            $(this).prop('disabled', true);
            // $('#btn-tabel').prop('disabled', false);
            $('#btn-pie').prop('disabled', false);

            $('#grafik').show();
            $('#pie').hide()
            // $('#statistik').hide()
        })

        $('#btn-pie').click(function() {
            $(this).prop('disabled', true);
            // $('#btn-tabel').prop('disabled', false);
            $('#btn-grafik').prop('disabled', false);

            $('#grafik').hide();
            $('#pie').show()
            // $('#statistik').hide()
        })

        function GetListTahun() {
            $('#filter_tahun').empty().trigger("change");
            var optionEmpty = new Option("", "");
            $("#filter_tahun").append(optionEmpty);
            $.ajax({
                type: 'GET',
                url: "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-tahun' }}",
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

        function GetListKabupaten() {
            $('#filter_kabupaten').empty().trigger("change");
            var optionEmpty = new Option("", "");
            $("#filter_kabupaten").append(optionEmpty);
            $.ajax({
                type: 'GET',
                url: "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-kabupaten' }}",
                dataType: 'json',
                success: function(data) {
                    for (var i = 0; i < data.length; i++) {
                        var newOption = new Option(data[i].nama_kabupaten, data[i].kode_kabupaten, true, true);
                        $("#filter_kabupaten").append(newOption);
                    }
                    $("#filter_kabupaten").val("").trigger("change");
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    if (textStatus === "timeout") {
                        alert(
                            "Permintaan data kabupaten gagal karena waktu koneksi habis (timeout). Silakan coba lagi."
                            );
                    } else {
                        try {
                            var responseJSON = JSON.parse(jqXHR.responseText);
                            alert("Terjadi kesalahan: " + responseJSON.message);
                        } catch (e) {
                            alert("Terjadi kesalahan tidak terduga: " + errorThrown);
                        }
                    }
                }
            });
        }

        function GetListKecamatan($id) {
            $('#filter_kecamatan').empty().trigger("change");
            if ($id != undefined && $id != null) {
                var optionEmpty = new Option("", "");
                $("#filter_kecamatan").append(optionEmpty);
                $.ajax({
                    type: 'GET',
                    url: "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-kecamatan' }}" +
                        "/" + $id,
                    dataType: 'json',
                    success: function(data) {
                        for (var i = 0; i < data.length; i++) {
                            var newOption = new Option(data[i].nama_kecamatan, data[i].kode_kecamatan, true,
                                true);
                            $("#filter_kecamatan").append(newOption);
                        }
                        $("#filter_kecamatan").val("");
                    },
                    error: function(jqXHR, textStatus, errorThrown) {

                    }
                });
            }
        }

        function GetListDesa($id) {
            $('#filter_desa').empty().trigger("change");
            if ($id != undefined && $id != null) {
                var optionEmpty = new Option("", "");
                $("#filter_desa").append(optionEmpty);
                $.ajax({
                    type: 'GET',
                    url: "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-desa' }}" + "/" +
                        $id,
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


        var summaryPenduduk = $('#summary-penduduk').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: false,
            searchPanes: {
                viewTotal: false,
                columns: [0]
            },
            ajax: {
                url: new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/wilayah/penduduk' }}"),
                method: 'get',
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[search]": row.search.value,
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
                    targets: 0,
                    render: function(data, type, row, meta) {
                        var PageInfo = $('#summary-penduduk').DataTable().page.info();
                        return PageInfo.start + meta.row + 1;
                    }
                },
                {
                    targets: [0, 1, 2, 3],
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
                    data: "attributes.nama_kecamatan",
                    name: "nama_kecamatan"
                },
                {
                    data: "attributes.penduduk_count",
                    name: "penduduk_count",
                    className: 'text-center'
                },
            ],
        })
    </script>
@endpush
