@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Jaminan Sosial')

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')

    <div class="row">
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    Statistik Jenis Bantuan
                </div>
                <div class="card-body">
                    <div class="chart" id="pie1" data-testid="chart-pie-bantuan">

                    </div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    Statistik Jenis Gangguan Mental
                </div>
                <div class="card-body">
                    <div class="chart" id="pie2" data-testid="chart-pie-mental">

                    </div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card">
                <div class="card-header">
                    Statistik Jenis Penanganan
                </div>
                <div class="card-body">
                    <div class="chart" id="pie4" data-testid="chart-pie-penanganan">

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
                        <x-filter-tahun />
                        <x-filter-status-presisi />
                        <div class="col-auto">
                            <x-print-button :print-url="route('jaminan-sosial-cetak')" table-id="jaminanSosial" :filter="[]" testId="btn-cetak" />
                        </div>
                        <x-excel-download-button :download-url="config('app.databaseGabunganUrl') . '/api/v1/data-presisi/jaminan-sosial/rtm/download'" table-id="jaminanSosial" filename="data_presisi_jaminan-sosial" :additional-params="[
                                ['key' => 'kode_kabupaten', 'value' => session('kabupaten.kode_kabupaten') ?? ''],
                                ['key' => 'kode_kecamatan', 'value' => session('kecamatan.kode_kecamatan') ?? ''],
                                ['key' => 'config_desa', 'value' => session('desa.id') ?? ''],
                            ]" testId="btn-export-excel" />
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="jaminanSosial" data-testid="datatable-data-pokok-jaminan-sosial">
                            <thead>
                                <tr>
                                    <th>Aksi</th>
                                    <th>#</th>
                                    <th>NIK</th>
                                    <th>Nama Kepala Keluarga</th>
                                    <th>Jumlah Anggota RTM</th>
                                    <th>Jenis Bantuan Sosial<br> Yang Pernah Diterima</th>
                                    <th>Jenis Gangguan Mental<br> Yang Diderita</th>
                                    <th>Jenis Penanganan <br>Penderita Gangguan Mental</th>
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
    <script nonce="{{ csp_nonce() }}">
        let transformedIncluded = {};
        document.addEventListener("DOMContentLoaded", function(event) {
            const header = @include('layouts.components.header_bearer_api_gabungan');
            @php
                $kodeKabupaten = session('kabupaten.kode_kabupaten') ?? '';
                $kodeKecamatan = session('kecamatan.kode_kecamatan') ?? '';
                $configDesa = session('desa.id') ?? '';
            @endphp
            const kodeKabupaten = "{{ $kodeKabupaten }}";
            const kodeKecamatan = "{{ $kodeKecamatan }}";
            const configDesa = "{{ $configDesa }}";
            var url = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/data-presisi/jaminan-sosial/rtm' }}");
            url.searchParams.set("kode_kabupaten", "{{ session('kabupaten.kode_kabupaten') ?? '' }}");
            url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
            url.searchParams.set("config_desa", "{{ session('desa.id') ?? '' }}");
            var jaminanSosial = $('#jaminanSosial').DataTable({
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
                            "sort": "id",
                            "filter[search]": row.search.value,                            
                            "filter[tahun]": $('#filter-tahun').val(),
                            "filter[status_kelengkapan]": $('#filter-status-kelengkapan').val(),
                        };
                    },
                    dataSrc: function(json) {
                        json.recordsTotal = json.meta?.pagination?.total || 0
                        json.recordsFiltered = json.meta?.pagination?.total || 0
                        if (json.data.length > 0) {                        
                            grafikPie({ kodeKabupaten, kodeKecamatan, configDesa })
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
                                 'rtm_id': data.id,
                                'no_kartu_rumah': d.no_kk,
                                'nama_kepala_keluarga': d.kepala_keluarga,
                                'alamat': d.alamat,
                                'jumlah_anggota': d.jumlah_anggota,
                                'jumlah_kk': d.jumlah_kk,
                            }
                            let jsonData = encodeURIComponent(JSON.stringify(obj));
                            const _url =
                                "{{ route('jaminan-sosial-detail', ['data' => '__DATA__']) }}"
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
                        orderable: false,
                    },
                    {
                        data: "attributes.kepala_keluarga",
                        orderable: false,
                    },
                    {
                        data: "attributes.jumlah_anggota",
                        orderable: false,
                    },
                    {
                        data: "attributes.jns_bantuan",
                        name: "jns_bantuan",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "attributes.jns_gangguan_mental",
                        name: "jns_gangguan_mental",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "attributes.terapi_gangguan_mental",
                        name: "terapi_gangguan_mental",
                        orderable: false,
                        searchable: false
                    },
                ],
            })

            // Add event listener for opening and closing details
            jaminanSosial.on('click', 'td.details-control', function() {
                let tr = $(this).closest('tr');
                let row = jaminanSosial.row(tr);
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

            $('#filter-tahun, #filter-status-kelengkapan').on('change', function() {
                jaminanSosial.ajax.reload();
                grafikPie({ kodeKabupaten, kodeKecamatan, configDesa });
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
        })
    </script>
@endsection
