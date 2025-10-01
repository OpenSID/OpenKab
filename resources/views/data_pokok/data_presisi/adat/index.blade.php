@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data adat')

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">Statistik Adat</div>
                <div class="card-body">
                    <div>
                        <div class="chart" id="pie1">

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
                    <div class="row">
                        <div class="col-sm-2">
                            <select id="filter-tahun" class="form-control form-control-sm">
                                @php
                                    $currentYear = date('Y');
                                    $startYear = 2020;
                                @endphp
                                @for($year = $currentYear; $year >= $startYear; $year--)
                                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <button id="cetak" type="button" class="btn btn-primary btn-sm" data-url="">
                                <i class="fa fa-print"></i> Cetak
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="adat">
                            <thead>
                                <tr>
                                    <th>Aksi</th>
                                    <th>#</th>
                                    <th>NIK</th>
                                    <th>Nama Kepala Keluarga</th>
                                    <th>Jumlah Anggota RTM</th>
                                    <th>Status Keanggotaan</th>
                                    <th>Frekwensi Mengikuti Kegiatan Adat Dalam Setahun</th>
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
@include('data_pokok.data_presisi.adat.chart')
    <script nonce="{{ csp_nonce() }}">
        let data_grafik = [];
        let transformedIncluded = {};
        document.addEventListener("DOMContentLoaded", function(event) {
            const header = @include('layouts.components.header_bearer_api_gabungan');
            var url = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/data-presisi/adat' }}");
            url.searchParams.set("kode_kabupaten", "{{ session('kabupaten.kode_kabupaten') ?? '' }}");
            url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
            url.searchParams.set("config_desa", "{{ session('desa.id') ?? '' }}");
            var adat = $('#adat').DataTable({
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
                    headers: header,
                    method: 'get',
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            'include': 'anggota,penduduk,rtm,keluarga',
                            "filter[search]": row.search.value,
                            "filter[kepala_rtm]": true,
                            // "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]
                            //         ?.column]
                            //     ?.name,
                            "filter[kode_desa]": $("#kode_desa").val(),
                            "filter[tahun]": $('#filter-tahun').val(),
                        };
                    },
                    dataSrc: function(json) {

                        if (json.data.length > 0) {
                            json.recordsTotal = json.meta.pagination.total
                            json.recordsFiltered = json.meta.pagination.total
                            data_grafik = [];
                            // Transform the included array into an object
                            transformedIncluded = json.included.reduce((acc, item) => {
                                if (!acc[item.type]) {
                                    acc[item.type] = {};
                                }
                                acc[item.type][item.id] = item.attributes;
                                return acc;
                            }, {});

                            json.data.forEach(function(item, index) {
                                data_grafik.push(item.attributes)
                                item.attributes.nik = transformedIncluded.penduduk[item
                                    .relationships.penduduk.data.id].nik;
                                item.attributes.nama = transformedIncluded.penduduk[item
                                    .relationships.penduduk.data.id].nama;
                                if (!item.attributes.frekwensi) {
                                    item.attributes.frekwensi = 'TIDAK TAHU'
                                }
                                if (!item.attributes.status_keanggotaan) {
                                    item.attributes.status_keanggotaan = 'TIDAK TAHU'
                                }
                                item.attributes.dtks = transformedIncluded.rtm[item
                                        .relationships.rtm.data.id].dtks ? 'Terdaftar' :
                                    'Tidak Terdaftar';
                                item.attributes.tgl_daftar = transformedIncluded.rtm[item
                                    .relationships.rtm.data.id].tgl_daftar;
                                item.attributes.jumlah_kk = transformedIncluded.rtm[item
                                    .relationships.rtm.data.id].jumlah_kk;
                                item.attributes.alamat = transformedIncluded.keluarga[item
                                    .relationships.keluarga.data.id].alamat;
                                item.attributes.dusun = transformedIncluded.keluarga[item
                                    .relationships.keluarga.data.id].wilayah?.dusun;
                                item.attributes.rt = transformedIncluded.keluarga[item
                                    .relationships.keluarga.data.id].wilayah?.rt;
                                item.attributes.rw = transformedIncluded.keluarga[item
                                    .relationships.keluarga.data.id].wilayah?.rw;
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
                        data: function(data) {
                            let d = data.attributes
                            let obj = {
                                'rtm_id': data.relationships.rtm.data.id,
                                'no_kartu_rumah': transformedIncluded.rtm[data.relationships.rtm
                                    .data.id].no_kk,
                                'nama_kepala_keluarga': d.nama,
                                'alamat': transformedIncluded.keluarga[data.relationships
                                    .keluarga.data.id].alamat,
                                'jumlah_anggota': d.anggota_count,
                                'jumlah_kk': transformedIncluded.rtm[data.relationships.rtm
                                    .data.id].jumlah_kk,
                            }
                            let jsonData = encodeURIComponent(JSON.stringify(obj));
                            const _url =
                                "{{ route('data-pokok.data-presisi-adat.detail', ['data' => '__DATA__']) }}"
                                .replace('__DATA__', jsonData)
                            return `<a href="${_url}" title="Detail" data-button="Detail">
                                <button type="button" class="btn btn-info btn-sm">Detail</button>
                            </a>`;
                        },
                        searchable: false,
                        orderable: false
                    },
                    {
                        "className": 'details-control',
                        "orderable": false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {
                        data: "attributes.nik",
                        name: "penduduk.nik",
                    },
                    {
                        data: "attributes.nama",
                        name: "rtm.nama_kepala_keluarga",
                        orderable: false
                    },
                    {
                        data: "attributes.anggota_count",
                        name: null,
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "attributes.status_keanggotaan",
                        name: "status_keanggotaan",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "attributes.frekwensi",
                        name: "frekwensi_mengikuti_kegiatan_setahun",
                        orderable: false,
                        searchable: false
                    },
                ],
            })

            // Add event listener for opening and closing details
            adat.on('click', 'td.details-control', function() {
                let tr = $(this).closest('tr');
                let row = adat.row(tr);
                if (row.child.isShown()) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    // Open this row
                    row.child(format(row.data())).show();
                    tr.addClass('shown');
                }
            });

            function format(data) {
                return `
                    <table class="table table-striped">
                        <tr>
                            <td><strong>DTKS:</strong></td>
                            <td>${data.attributes.dtks || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Jumlah KK:</strong></td>
                            <td>${data.attributes.jumlah_kk || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Alamat:</strong></td>
                            <td>${data.attributes.alamat || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Dusun:</strong></td>
                            <td>${data.attributes.dusun || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>RT:</strong></td>
                            <td>${data.attributes.rt || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>RW:</strong></td>
                            <td>${data.attributes.rw || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Terdaftar:</strong></td>
                            <td>${data.attributes.tgl_daftar || 'N/A'}</td>
                        </tr>
                    </table>
                `;
            }
            // Event listener for year filter change
            $('#filter-tahun').on('change', function() {
                adat.ajax.reload();
                data_grafik = [];
                grafikPie();
            });
            
            $('#cetak').on('click', function() {
                let baseUrl = "{{ route('data-pokok.data-presisi-adat.cetak') }}";
                let params = adat.ajax.params(); // Get DataTables params
                let queryString = new URLSearchParams(params).toString(); // Convert params to query string
                window.open(`${baseUrl}?${queryString}`, '_blank'); // Open the URL with appended query
            });
        })
    </script>
@endsection
