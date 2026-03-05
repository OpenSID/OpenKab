@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Agama')

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">Statistik Agama</div>
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
                        <x-filter-tahun />
                        <x-filter-status-presisi />
                        <div class="col-auto">
                            <x-print-button :print-url="route('cetak_agama')" table-id="agama" :filter="[]" />
                        </div>
                        <x-excel-download-button :download-url="config('app.databaseGabunganUrl') . '/api/v1/data-presisi/agama/rtm/download'" table-id="agama" filename="data_presisi_agama"
                            :additional-params="[
                                ['key' => 'kode_kabupaten', 'value' => session('kabupaten.kode_kabupaten') ?? ''],
                                ['key' => 'kode_kecamatan', 'value' => session('kecamatan.kode_kecamatan') ?? ''],
                                ['key' => 'config_desa', 'value' => session('desa.id') ?? ''],
                            ]" />
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="agama">
                            <thead>
                                <tr>
                                    <th>Aksi</th>
                                    <th>#</th>
                                    <th>NIK</th>
                                    <th>Nama Kepala Keluarga</th>
                                    <th>Jumlah Anggota RTM</th>
                                    <th>Agama</th>
                                    <th>Frekwensi Mengikuti Kegiatan Keagamaan Dalam Setahun</th>
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
    @include('data_pokok.agama.chart')
    <script nonce="{{ csp_nonce() }}">
        let data_grafik = [];
        let transformedIncluded = {};
        document.addEventListener("DOMContentLoaded", function(event) {
            const header = @include('layouts.components.header_bearer_api_gabungan');
            var url = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/data-presisi/agama/rtm' }}");
            url.searchParams.set("kode_kabupaten", "{{ session('kabupaten.kode_kabupaten') ?? '' }}");
            url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
            url.searchParams.set("config_desa", "{{ session('desa.id') ?? '' }}");
            var agama = $('#agama').DataTable({
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
                            "filter[kode_desa]": $("#kode_desa").val(),
                            "filter[tahun]": $("#filter-tahun").val(),
                            "filter[status_kelengkapan]": $('#filter-status-kelengkapan').val(),
                        };
                    },
                    dataSrc: function(json) {
                        json.recordsTotal = json.meta?.pagination?.total || 0
                        json.recordsFiltered = json.meta?.pagination?.total || 0
                        if (json.data.length > 0) {                        
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
                                "{{ route('detail_agama', ['data' => '__DATA__']) }}"
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
                        data: "attributes.agama",
                        name: "agama",
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: "attributes.frekwensi_mengikuti_kegiatan",
                        name: "frekwensi_mengikuti_kegiatan",
                        orderable: false,
                        searchable: false
                    },
                ],
            })

            // Add event listener for opening and closing details
            agama.on('click', 'td.details-control', function() {
                let tr = $(this).closest('tr');
                let row = agama.row(tr);
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

            $('#filter-tahun, #filter-status-kelengkapan').on('change', function() {
                agama.ajax.reload();
                data_grafik = [];
                grafikPie();
            });
        })
    </script>
@endsection
