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
                <div class="card-header">Statistik Golongan Darah</div>
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
                <div class="card-header">Statistik Status Gizi Balita</div>
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
                        <table class="table table-striped" id="kesehatan">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>NIK</th>
                                    <th>Golongan Darah</th>
                                    <th>Cacat</th>
                                    <th>Sakit Menahun</th>
                                    <th>Akseptor KB</th>
                                    <th>Status Kehamilan</th>
                                    <th>Asuransi Kesehatan</th>
                                    <th>Status Gizi Balita</th>
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
    @include('data_pokok.kesehatan.chart')
    <script nonce="{{ csp_nonce() }}"  >
        let data_grafik = [];        
    document.addEventListener("DOMContentLoaded", function(event) {
        const header = @include('layouts.components.header_bearer_api_gabungan');
        var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/data/kesehatan' }}");
        url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
        url.searchParams.set("config_desa", "{{ session('desa.id') ?? '' }}");
        var kesehatan = $('#kesehatan').DataTable({
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
                headers: header,
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
                        grafik()
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
                    data: "attributes.golongan_darah",
                    name: "golongan_darah",
                    orderable: false
                },
                {
                    data: "attributes.cacat",
                    name: "cacat",
                    orderable: false
                },
                {
                    data: "attributes.sakit_menahun",
                    name: "sakit_menahun",
                    orderable: false
                },
                {
                    data: "attributes.kb",
                    name: "kb",
                    orderable: false
                },
                {
                    data: "attributes.hamil",
                    name: "hamil",
                    orderable: false
                },
                {
                    data: "attributes.asuransi",
                    name: "asuransi",
                    orderable: false
                },
                {
                    data: "attributes.status_gizi",
                    name: "status_gizi",
                    orderable: false
                },
            ],
            order: [
                [0, 'asc']
            ]
        })
        kesehatan.on('draw.dt', function() {
            var PageInfo = $('#kesehatan').DataTable().page.info();
            kesehatan.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });
    })
    </script>
@endsection