@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Bantuan')

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Statistik Jumlah Penginapan</div>
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
                <div class="card-header">Statistik Tingkat Pemanfaatan</div>
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
                        <table class="table table-striped" id="pariwisata">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>{{ config('app.sebutanDesa') }}</th>
                                    <th>Jenis Hiburan</th>
                                    <th>Jumlah Penginapan</th>
                                    <th>Lokasi/Tempat/Area Wisata</th>
                                    <th>Keberadaan</th>
                                    <th>Luas (Ha)</th>
                                    <th>Tingkat Pemanfaatan</th>
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
    @include('data_pokok.pariwisata.chart')
    <script nonce="{{ csp_nonce() }}">
        let data_grafik = [];


        document.addEventListener("DOMContentLoaded", function(event) {

            const header = @include('layouts.components.header_bearer_api_gabungan');
            var url = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/pariwisata' }}");
            url.searchParams.set("kode_kabupaten", "{{ session('kabupaten.kode_kabupaten') ?? '' }}");
            url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
            url.searchParams.set("config_desa", "{{ session('desa.id') ?? '' }}");

            var pariwisata = $('#pariwisata').DataTable({
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
                    headers: header,
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[search]": row.search.value,
                            "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]
                                    ?.column]
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
                }, ],
                columns: [{
                        data: null,
                    },
                    {
                        data: "attributes.nama_desa",
                        name: "desa",
                        orderable: false
                    },
                    {
                        data: "attributes.jenis_hiburan",
                        name: "jenis_hiburan",
                        orderable: false
                    },
                    {
                        data: "attributes.jumlah_penginapan",
                        name: "jumlah_penginapan",
                        orderable: false
                    },
                    {
                        data: "attributes.lokasi_tempat_area_wisata",
                        name: "lokasi_tempat_area_wisata",
                        className: 'text-center',
                        orderable: false
                    },
                    {
                        data: "attributes.keberadaan",
                        name: "keberadaan",
                        orderable: false
                    },
                    {
                        data: "attributes.luas",
                        name: "luas",
                        orderable: false
                    },
                    {
                        data: "attributes.tingkat_pemanfaatan",
                        name: "tingkat_pemanfaatan",
                        orderable: false
                    },
                ],
                order: [
                    [0, 'asc']
                ]
            })

            pariwisata.on('draw.dt', function() {
                var PageInfo = $('#pariwisata').DataTable().page.info();
                pariwisata.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + PageInfo.start;
                });
            });


        })
    </script>
@endsection
