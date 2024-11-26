@extends('layouts.index')

@section('title', 'Data Papan')

@section('content_header')
    <h1>Data Papan</h1>
@stop

@push('css')
    <style>
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
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-3">
                            <button id="cetak" type="button" class="btn btn-primary btn-sm" data-url="">
                                <i class="fa fa-print"></i> Cetak
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-dtks">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>NIK</th>
                                    <th>Status Kepemilikan</th>
                                    <th>Luas Lantai (m²)</th>
                                    <th>Jenis Lantai</th>
                                    <th>Jenis Dinding</th>
                                    <th>Sumber Air Minum</th>
                                    <th>Sumber Penerangan</th>
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
    <script nonce="{{ csp_nonce() }}"  >
    document.addEventListener("DOMContentLoaded", function(event) {
        var dtks = $('#table-dtks').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: false,
            searchPanes: {
                viewTotal: false,
                columns: [0]
            },
            ajax: {
                url: `{{ url('api/v1/satu-data/dtks') }}`,
                method: 'get',
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[search]": row.search.value,
                        "kode_kecamatan": "{{ session('kecamatan.kode_kecamatan') ?? '' }}",
                        "config_desa": "{{ session('desa.id') ?? '' }}",
                    };
                },
                dataSrc: function(json) {
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
                    targets: [0, 2, 3, 4, 5, 6, 7],
                    orderable: false,
                    searchable: false,
                },
            ],
            columns: [
                {
                    "className": 'details-control',
                    "orderable": false,
                    "data": null,
                    "defaultContent": ''
                },
                {
                    data: "attributes.nik_kepala_rtm",
                    name: "rtm.kepalaKeluarga.nik",
                    orderable: false,
                    render: (data) => data || 'N/A',
                },
                {
                    data: "attributes.status_kepemilikan_bangunan_tempat_tinggal_yang_ditempati",
                    render: (data) => data || 'N/A',
                },
                {
                    data: "attributes.luas_lantai_m2",
                    render: (data) => data || 'N/A',
                },
                {
                    data: "attributes.jenis_lantai_terluas",
                    render: (data) => data || 'N/A',
                },
                {
                    data: "attributes.jenis_dinding_terluas",
                    name: "nama_sasaran",
                    render: (data) => data || 'N/A',
                },
                {
                    data: "attributes.sumber_air_minum",
                    name: "nama_sasaran",
                    render: (data) => data || 'N/A',
                },
                {
                    data: "attributes.sumber_penerangan_utama",
                    render: (data) => data || 'N/A',
                },
            ],
        })

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
                        <td><strong>NIK Kepala RTM:</strong></td>
                        <td>${data.attributes.nik_kepala_rtm || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Status Kepemilikan:</strong></td>
                        <td>${data.attributes.status_kepemilikan_bangunan_tempat_tinggal_yang_ditempati || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Luas Lantai (m²):</strong></td>
                        <td>${data.attributes.luas_lantai_m2 || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Jenis Lantai Terluas:</strong></td>
                        <td>${data.attributes.jenis_lantai_terluas || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Jenis Dinding Terluas:</strong></td>
                        <td>${data.attributes.jenis_dinding_terluas || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Sumber Air Minum:</strong></td>
                        <td>${data.attributes.sumber_air_minum || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Sumber Penerangan:</strong></td>
                        <td>${data.attributes.sumber_penerangan_utama || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Bahan Bakar untuk Memasak:</strong></td>
                        <td>${data.attributes.bahan_bakar_energi_utama_untuk_memasak || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Tempat Pembuangan Akhir Tinja:</strong></td>
                        <td>${data.attributes.tempat_pembuangan_akhir_tinja  || 'N/A'}</td>
                    </tr>
                </table>
            `;
        }

        $('#cetak').on('click', function() {
            let baseUrl = "{{ url('satu-data/dtks/cetak') }}";
            let params = dtks.ajax.params(); // Get DataTables params
            let queryString = new URLSearchParams(params).toString(); // Convert params to query string
            window.open(`${baseUrl}?${queryString}`, '_blank'); // Open the URL with appended query
        });
    })
    </script>
@endsection
