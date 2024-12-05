@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Bantuan')

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Statistik Tempat Ibadah</div>
                <div class="card-body">
                    <div>
                        <div class="chart" id="grafik">
                            <canvas id="barChart"></canvas>
                        </div>
                        <hr class="hr-chart">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">Statistik Agama & Suku</div>
                <div class="card-body">
                    <div class="row">
                        <!-- Chart Kiri -->
                        <div class="col-md-6">
                            <div class="chart" id="pie1">
                                <canvas id="donutChart1"></canvas>
                            </div>
                        </div>
                        <!-- Chart Kanan -->
                        <div class="col-md-6">
                            <div class="chart" id="pie2">
                                <canvas id="donutChart2"></canvas>
                            </div>
                        </div>
                    </div>
                    <hr class="hr-chart">
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="float-left">Jenis Tempat Ibadah</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="tempatibadah">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Tempat Ibadah</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="float-left">Data Perorangan</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="dataperorangan">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>NIK</th>
                                    <th>Agama</th>
                                    <th>Suku</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="float-left">Data kelembagaan Desa - Desa A</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="kelembagaan">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Kategori</th>
                                    <th>Detail</th>
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

@section('js')
    @include('data_pokok.agama_adat.chart')
    <script nonce="{{ csp_nonce() }}"  >
        let data_grafik_pie = [];
        let data_grafik_bar = [];
    document.addEventListener("DOMContentLoaded", function(event) {

        var url = new URL("{{ url('api/v1/prodeskel/potensi/kelembagaan') }}");
        url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
        url.searchParams.set("config_desa", "{{ session('desa.id') ?? '' }}");

        var tempatibadah = $('#tempatibadah').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            paging: false,
            searching: false,
            searchPanes: {
                viewTotal: false,
                columns: [0]
            },
            ajax: {
                url: url.href,
                method: 'get',
                dataSrc: function(json) {

                    if (json.data.length > 0) {
                        let obj = [];
                        data_grafik = [];

                        json.data.forEach(function(item, index) {

                            let sarana = item.attributes.data.prasarana_peribadatan
                            
                            // convert untuk kebutuhan grafik
                            sarana.forEach(function(i, key) {
                                data_grafik_bar.push(i.data)
                            })

                            // masukkan ke variabel object agar mudah di looping
                            obj = sarana

                        })
                        grafikBar()
                        return obj;
                    }
                    return false;
                },
            },
            columnDefs: [{
                        targets: '_all',
                        className: 'text-nowrap',
                    },
                ],
            columns: [{
                    data: null,
                },
                {
                    data: "data.jenis_tempat_ibadah",
                    name: "Jenis Tempat Ibadah",
                    orderable: false
                },
                {
                    data: "data.jumlah",
                    name: "jumlah",
                    orderable: false
                }
            ],
            order: [
                [0, 'asc']
            ]
        })
        tempatibadah.on('draw.dt', function() {
            var PageInfo = $('#tempatibadah').DataTable().page.info();
            tempatibadah.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        var dataperorangan = $('#dataperorangan').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            searchPanes: {
                viewTotal: false,
                columns: [0]
            },
            ajax: {
                url: url.href,
                method: 'get',
                // data: function(row) {

                //     return {
                //         "page[size]": row.length,
                //         "page[number]": (row.start / row.length) + 1,
                //         "filter[search]": row.search.value,
                //         "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]
                //             ?.name,
                //         "filter[kode_desa]": $("#kode_desa").val(),
                //     };
                // },
                dataSrc: function(json) {

                    if (json.data.length > 0) {
                        let obj = [];
                        data_grafik = [];

                        json.data.forEach(function(item, index) {

                            let penduduk = item.attributes.data.penduduk

                            // convert untuk kebutuhan grafik
                            penduduk.data.forEach(function(i, key) {
                                data_grafik_pie.push(i.attributes)
                            })

                            // mengambil pagination nested
                            json.recordsTotal = penduduk.meta.pagination.total
                            json.recordsFiltered = penduduk.meta.pagination.total

                            // masukkan ke variabel object agar mudah di looping
                            obj = penduduk.data

                        })
                        grafikPie()
                        return obj;
                    }
                    return false;
                },
            },
            columnDefs: [{
                        targets: '_all',
                        className: 'text-nowrap',
                    },
                ],
            columns: [{
                    data: null,
                },
                {
                    data: "attributes.nik", // Akses data penduduk
                    name: "nik",
                    orderable: false
                },
                {
                    data: "attributes.agama",
                    name: "agama",
                    orderable: false
                },
                {
                    data: "attributes.suku",
                    name: "suku",
                    orderable: false
                }
            ],
            order: [
                [0, 'asc']
            ]
        })
        dataperorangan.on('draw.dt', function() {
            var PageInfo = $('#dataperorangan').DataTable().page.info();
            dataperorangan.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        // fetch kelembagaan
        var kelembagaan = $('#kelembagaan').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            paging: false,
            searching: false,
            searchPanes: {
                viewTotal: false,
                columns: [0]
            },
            ajax: {
                url: url.href,
                method: 'get',
                dataSrc: function(json) {

                    if (json.data.length > 0) {
                        json.recordsTotal = json.meta.pagination.total;
                        json.recordsFiltered = json.meta.pagination.total;

                        // Array untuk menampung semua data yang akan dikirim ke DataTables
                        let tableData = [];

                        json.data.forEach(function(item) {
                            let fields = [
                                'pemangku_adat',
                                'kepengurusan_adat',
                                'rumah_adat',
                                'barang_pusaka',
                                'naskah',
                                'lainnya',
                                'musyawarah_adat',
                                'sanksi_adat',
                                'upacara_adat_perkawinan',
                                'upacara_adat_kematian',
                                'upacara_adat_kelahiran',
                                'upacara_adat_cocok_tanam',
                                'upacara_adat_perikanan',
                                'upacara_adat_kehutanan',
                                'upacara_adat_sda',
                                'upacara_adat_pembangunan',
                                'upacara_adat_penyelesaian_masalah'
                            ];

                            let data = item.attributes.data;

                            fields.forEach(function(field) {
                                if (data.hasOwnProperty(field)) {
                                    // Format nama kategori
                                    let formattedField = field
                                        .split('_') // Pisahkan berdasarkan _
                                        .map(word => word.charAt(0).toUpperCase() + word.slice(1)) // Kapitalisasi
                                        .join(' '); // Gabungkan kembali dengan spasi

                                    // Tambahkan setiap kategori sebagai satu baris di tabel
                                    tableData.push({
                                        kategori: formattedField, // Gunakan format baru
                                        value: data[field]
                                    });
                                }
                            });
                        });

                        return tableData;
                    }
                    return false;
                },
            },
            columnDefs: [{
                        targets: '_all',
                        className: 'text-nowrap',
                    },
                ],
            columns: [{
                    data: null,
                },
                {
                    data: "kategori",
                    name: "Kategori",
                    orderable: false
                },
                {
                    data: "value",
                    name: "Detail",
                    orderable: false
                },
            ],
            order: [
                [0, 'asc']
            ]
        })
        kelembagaan.on('draw.dt', function() {
            var PageInfo = $('#kelembagaan').DataTable().page.info();
            kelembagaan.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });
    })
    </script>
@endsection