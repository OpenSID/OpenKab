@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Pendidikan Penduduk dan DTKS')

@section('content_header')
    <h1>Data Pendidikan Penduduk dan DTKS</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Statistik Partisipasi Sekolah</div>
                <div class="card-body">
                    <div>
                        <div class="chart" id="pie">
                            <canvas id="donutChart"></canvas>
                        </div>
                        <hr class="hr-chart">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Statistik Ijazah Tertinggi</div>
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
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="float-left">Data Pendidikan Penduduk dan DTKS</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="pendidikan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Pendidikan dalam KK</th>
                                    <th>Pendidikan Sedang Ditempuh</th>
                                    <th>Partisipasi Sekolah</th>
                                    <th>Jenjangn dan Jenis Pendidikan Tertinggi</th>
                                    <th>Kelas Tertinggi</th>
                                    <th>Ijazah Tertinggi</th>
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
    @include('data_pokok.pendidikan.chart')
    <script nonce="{{ csp_nonce() }}"  >
        let data_grafik = [];
    document.addEventListener("DOMContentLoaded", function(event) {

        var url = new URL("{{ url('api/v1/pendidikan') }}");
        url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
        url.searchParams.set("config_desa", "{{ session('desa.id') ?? '' }}");

        var pendidikan = $('#pendidikan').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: false,
            searchPanes: {
                viewTotal: false,
                columns: [0]
            },
            ajax: {
                url: url.href,
                method: 'get',
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[search]": row.search.value,
                        "filter[kode_desa]": $("#kode_desa").val(),
                    };
                },
                dataSrc: function(json) {
                    
                    if (json.data.length > 0) {
                        json.recordsTotal = json.meta.pagination.total
                        json.recordsFiltered = json.meta.pagination.total
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
            columnDefs: [{
                        targets: '_all',
                        className: 'text-nowrap',
                    },
                ],
            columns: [{
                    data: null,
                    orderable: false
                },
                {
                    data: "attributes.nik",
                    name: "nik",
                    orderable: false
                },
                {
                    data: "attributes.pendidikan_kk_id",
                    name: "pendidikan_kk_id",
                    orderable: false
                },
                {
                    data: "attributes.pendidikan_sedang_id",
                    name: "pendidikan_sedang_id",
                    orderable: false
                },
                {
                    data: "attributes.partisipasi_sekolah",
                    name: "partisipasi_sekolah",
                    orderable: false
                },
                {
                    data: "attributes.pendidikan_tertinggi",
                    name: "pendidikan_tertinggi",
                    orderable: false
                },
                {
                    data: "attributes.kelas_tertinggi",
                    name: "kelas_tertinggi",
                    orderable: false
                },
                {
                    data: "attributes.ijazah_tertinggi",
                    name: "ijazah_tertinggi",
                    orderable: false
                },
            ]
        })
        pendidikan.on('draw.dt', function() {
            var PageInfo = $('#pendidikan').DataTable().page.info();
            pendidikan.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });
    })
    </script>
@endsection