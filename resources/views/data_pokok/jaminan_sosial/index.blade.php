@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Bantuan')

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Statistik Kepesertaan DTKS</div>
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
                <div class="card-header">Statistik Jaminan Kesehatan</div>
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
                    <div class="float-left">{{ $title }}</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="jaminansosial">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>NIK</th>
                                    <th>Terdaftar DTKS</th>
                                    <th>Memiliki Jaminan Kesehatan</th>
                                    <th>Program Pra-Kerja</th>
                                    <th>Program KUR</th>
                                    <th>Program Ultra Mikro</th>
                                    <th>Jaminan Ketenagakerjaan</th>
                                    <th>Cacat</th>
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
    @include('data_pokok.jaminan_sosial.chart')
    <script nonce="{{ csp_nonce() }}"  >
        let data_grafik = [];
    document.addEventListener("DOMContentLoaded", function(event) {
        var url = new URL("{{ url('api/v1/data/jaminan-sosial') }}");
        url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
        url.searchParams.set("config_desa", "{{ session('desa.id') ?? '' }}");
        var jaminansosial = $('#jaminansosial').DataTable({
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
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[search]": row.search.value,
                        "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]
                            ?.name,
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
                },
                {
                    data: "attributes.nik",
                    name: "nik",
                    orderable: false
                },
                {
                    data: "attributes.dtks",
                    name: "dtks",
                    orderable: false
                },
                {
                    data: "attributes.asuransi",
                    name: "asuransi",
                    orderable: false
                },
                {
                    data: "attributes.kd_ikut_prakerja",
                    name: "kd_ikut_prakerja",
                    orderable: false
                },
                {
                    data: "attributes.kd_kur",
                    name: "kd_kur",
                    orderable: false
                },
                {
                    data: "attributes.kd_umi",
                    name: "kd_umi",
                    orderable: false
                },
                {
                    data: "attributes.bpjs_ketenagakerjaan",
                    name: "bpjs_ketenagakerjaan",
                    orderable: false
                },
                {
                    data: "attributes.cacat",
                    name: "cacat",
                    orderable: false
                },
            ],
            order: [
                [0, 'asc']
            ]
        })
        jaminansosial.on('draw.dt', function() {
            var PageInfo = $('#jaminansosial').DataTable().page.info();
            jaminansosial.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });
    })
    </script>
@endsection