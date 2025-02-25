@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Pekerjaan dan Pelatihan')

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Statistik Jumlah Penghasilan</div>
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
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Statistik Pelatihan</div>
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
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="float-left">{{ $title }}</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="ketenagakerjaan">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Pekerjaan</th>
                                    <th>Jabatan</th>
                                    <th>Jumlah Penghasilan</th>
                                    <th>Pelatihan</th>
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
    @include('data_pokok.ketenagakerjaan.chart')
    <script nonce="{{ csp_nonce() }}"  >
        let data_grafik = [];
    document.addEventListener("DOMContentLoaded", function(event) {

        var url = new URL("{{ url('api/v1/ketenagakerjaan') }}");
        url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
        url.searchParams.set("config_desa", "{{ session('desa.id') ?? '' }}");

        var ketenagakerjaan = $('#ketenagakerjaan').DataTable({
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
                    data: "attributes.pekerjaan",
                    name: "pekerjaan",
                    orderable: false
                },
                {
                    data: "attributes.jabatan",
                    name: "jabatan",
                    orderable: false
                },
                {
                    data: "attributes.jumlah_penghasilan",
                    name: "jumlah_penghasilan",
                    orderable: false
                },
                {
                    data: "attributes.pelatihan",
                    name: "pelatihan",
                    orderable: false
                },
            ],
        })
        ketenagakerjaan.on('draw.dt', function() {
            var PageInfo = $('#ketenagakerjaan').DataTable().page.info();
            ketenagakerjaan.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });
    })
    </script>
@endsection