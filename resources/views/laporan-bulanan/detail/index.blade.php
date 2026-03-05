@extends('layouts.index')

@section('title', $page_title ?? 'Page Title')

@section('content_header')
<h1>{{ $page_description ?? '' }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <form id="mainform" name="mainform" method="post">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm-12">
                                <a href="{{ route('laporan-bulanan.export-excel-detail', ['rincian' => $rincian, 'tipe' => $tipe]) }}"
                                    class="btn btn-success btn-sm">
                                    <i class="fa fa-file-excel"></i>
                                    Export Excel
                                </a>
                                <a href="{{ route('laporan-bulanan.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-arrow-left"></i>
                                    Kembali Ke Laporan Bulanan
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="text-center"><strong>RINCIAN LAPORAN PERKEMBANGAN {{ $title }}</strong></h5>
                        </br>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="detail_penduduk">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>NIK</th>
                                        <th>Tempat Lahir</th>
                                        <th>Tanggal Lahir</th>
                                        <th>Nama Ayah</th>
                                        <th>Nama Ibu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script nonce="{{ csp_nonce() }}">
        const header = @include('layouts.components.header_bearer_api_gabungan');
        var url = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/statistik/laporan-bulanan/sumber-data' }}");

        document.addEventListener("DOMContentLoaded", function (event) {
            let nama_desa = `{{ session('desa.nama_desa') }}`;
            var penduduk = $('#detail_penduduk').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
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
                    data: function (row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[search]": row.search.value,
                            "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]
                                ?.column]
                                ?.name,
                            'filter[rincian]': '{{ $rincian }}',
                            'filter[tipe]': '{{ $tipe }}',
                            'filter[tahun]': '{{ $tahun }}',
                            'filter[bulan]': '{{ $bulan }}',
                            'filter[kode_kabupaten]': '{{ $kode_kabupaten }}',
                            'filter[kode_kecamatan]': '{{ $kode_kecamatan }}',
                            'filter[kode_desa]': '{{ $kode_desa }}',
                            'kode_kabupaten': '{{ $kode_kabupaten }}',
                            'kode_kecamatan': '{{ $kode_kecamatan }}',
                            'kode_desa': '{{ $kode_desa }}',
                        };
                    },
                    dataSrc: function (json) {
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
                    targets: [0, 1, 4, 5, 6],
                    orderable: false,
                    searchable: false,
                },
                ],
                columns: [{
                    data: null,
                },
                {
                    data: "attributes.nama",
                    name: "nama",
                    defaultContent: '-',
                },
                {
                    data: "attributes.nik",
                    name: "nik",
                    defaultContent: '-',
                },
                {
                    data: "attributes.tempatlahir",
                    searcable: false,
                    className: 'text-center',
                    defaultContent: '-',
                },
                {
                    data: function (data) {
                        return data.attributes.tanggallahir ?? '';
                    },
                },
                {
                    data: "attributes.nama_ayah",
                    name: "nama_ayah",
                    defaultContent: '-',
                },
                {
                    data: "attributes.nama_ibu",
                    name: "nama_ibu",
                    defaultContent: '-',
                },
                ],
                order: [
                    [2, 'asc']
                ]
            })

            penduduk.on('draw.dt', function () {
                var PageInfo = $('#detail_penduduk').DataTable().page.info();
                penduduk.column(0, {
                    page: 'current'
                }).nodes().each(function (cell, i) {
                    cell.innerHTML = i + 1 + PageInfo.start;
                });
            });
        });
    </script>
@endsection