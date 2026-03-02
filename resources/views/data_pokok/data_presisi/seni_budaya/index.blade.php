@extends('layouts.index')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@push('css')
   <style nonce="{{ csp_nonce() }}" >
        .details {
            margin-left: 20px;
        }
    </style>
@endpush

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <div class="chart" id="grafik">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <x-filter-tahun />
                        <x-filter-status-presisi />
                        <div class="col-auto">
                            <x-print-button :print-url="url('data-presisi/seni-budaya/cetak')" table-id="table-seni-budaya" :filter="[]" />
                        </div>
                        <x-excel-download-button :download-url="config('app.databaseGabunganUrl') . '/api/v1/data-presisi/seni-budaya/rtm/download'" table-id="table-seni-budaya" filename="data_presisi_seni-budaya" />

                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-seni-budaya">
                            <thead>
                                <tr>
                                    <th>Aksi</th>
                                    <th>#</th>
                                    <th>NIK</th>
                                    <th>Nama Kepala Keluarga</th>
                                    <th>Jumlah Anggota RTM</th>
                                    <th>Jenis Seni yang dikuasai</th>
                                    <th>Jumlah penghasilan dari seni</th>
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
    @include('data_pokok.data_presisi.seni_budaya.chart')
    <script nonce="{{ csp_nonce() }}">
        let data_grafik = [];
        document.addEventListener("DOMContentLoaded", function(event) {
            const header = @include('layouts.components.header_bearer_api_gabungan');
            var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/seni-budaya/rtm' }}");
            url.searchParams.set("kode_kabupaten", "{{ session('kabupaten.kode_kabupaten') ?? '' }}");
            url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
            url.searchParams.set("kode_desa", "{{ session('desa.id') ?? '' }}");

            var dtks = $('#table-seni-budaya').DataTable({
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
                            "filter[search]": row.search.value,
                            "kode_kecamatan": "{{ session('kecamatan.kode_kecamatan') ?? '' }}",
                            "config_desa": "{{ session('desa.id') ?? '' }}",
                            "filter[tahun]": $('#filter-tahun').val(),
                            "filter[status_kelengkapan]": $('#filter-status-kelengkapan').val(),
                        };
                    },
                    dataSrc: function(json) {
                        // Set default values untuk recordsTotal dan recordsFiltered
                        json.recordsTotal = json.meta?.pagination?.total || 0;
                        json.recordsFiltered = json.meta?.pagination?.total || 0;
                        if (json.data && json.data.length > 0) {                            
                            data_grafik = [];
                            json.data.forEach(function(item, index) {
                                data_grafik.push(item.attributes);
                            });
                            grafikPie();  // Pastikan grafikPie() ada
                            return json.data;
                        }
                        return []; // Return empty array jika data kosong
                    },
                },
                columnDefs: [{
                    targets: '_all',
                    className: 'text-nowrap',
                }],
                columns: [
                    {
                        data: function(data) {
                            let d = data.attributes;
                            let obj = {
                                'rtm_id' : data.id,
                                'no_kartu_rumah': d.no_kk,
                                'nama_kepala_keluarga': d.kepala_keluarga,
                                'alamat': d.alamat,
                                'jumlah_anggota': d.jumlah_anggota,
                                'jumlah_kk': d.jumlah_kk,
                            };
                            let jsonData = encodeURIComponent(JSON.stringify(obj));
                            const _url =  "{{ route('data-pokok.data-presisi-seni-budaya.detail', ['data' => '__DATA__']) }}".replace('__DATA__', jsonData);
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
                    },
                    {
                        data: "attributes.jumlah_anggota",
                    },
                    {
                        data: "attributes.jenis_seni_yang_dikuasai.jenis_seni_value",
                        render: function(data) {
                            return data || 'N/A';
                        },
                    },
                    {
                        data: "attributes.jumlah_penghasilan_dari_seni",
                        render: function(data) {
                            if (!data || data === 'TIDAK TAHU') return 'N/A';
                            return 'Rp ' + parseInt(data).toLocaleString('id-ID');
                        },
                    },
                ],
            });

            // Add event listener for opening and closing details
            dtks.on('click', 'td.details-control', function () {
                let tr = $(this).closest('tr');
                let row = dtks.row(tr);
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
            $('#filter-tahun, #filter-status-kelengkapan').on('change', function() {
                dtks.ajax.reload();
                data_grafik = [];
                grafikPie();
            });
        });
    </script>
@endsection
