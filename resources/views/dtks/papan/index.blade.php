@extends('layouts.index')

@section('title', 'Data Papan')

@section('content_header')
    <h1>Data Papan</h1>
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
    @include('data_pokok.ketenagakerjaan.chart')
    <script nonce="{{ csp_nonce() }}">
        let data_grafik = [];
        document.addEventListener("DOMContentLoaded", function(event) {
            const header = @include('layouts.components.header_bearer_api_gabungan');
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
                    url: `{{ config('app.databaseGabunganUrl').'/api/v1/presisi/papan' }}`,
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
                        };
                    },
                    dataSrc: function(json) {
                        json.recordsTotal = json.meta.pagination.total
                        json.recordsFiltered = json.meta.pagination.total

                        // Extract chart data from API response
                        data_grafik = json.data.filter(item => item.attributes.kd_stat_bangunan_tinggal)
                            .map(item => ({
                                label: item.attributes.kd_stat_bangunan_tinggal,
                                value: 1 // Count each occurrence (can aggregate here)
                            }));

                        // Combine duplicate labels and aggregate values
                        //data_grafik = combineData(data_grafik);
                            
                        tampilChart('bar', 'barChart', generateChartData(data_grafik, 'label', 'Statistik Papan'));

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
                        data: "attributes.kd_stat_bangunan_tinggal",
                        render: (data) => data || 'N/A',
                    },
                    {
                        data: "attributes.luas_lantai",
                        render: (data) => data || 'N/A',
                    },
                    {
                        data: "attributes.kd_jenis_lantai_terluas",
                        render: (data) => data || 'N/A',
                    },
                    {
                        data: "attributes.kd_jenis_dinding",
                        name: "nama_sasaran",
                        render: (data) => data || 'N/A',
                    },
                    {
                        data: "attributes.kd_sumber_air_minum",
                        name: "nama_sasaran",
                        render: (data) => data || 'N/A',
                    },
                    {
                        data: "attributes.kd_sumber_penerangan_utama",
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
                            <td>${data.attributes.kd_stat_bangunan_tinggal || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Luas Lantai (m²):</strong></td>
                            <td>${data.attributes.luas_lantai || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Jenis Lantai Terluas:</strong></td>
                            <td>${data.attributes.kd_jenis_lantai_terluas || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Jenis Dinding Terluas:</strong></td>
                            <td>${data.attributes.kd_jenis_dinding || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Sumber Air Minum:</strong></td>
                            <td>${data.attributes.kd_sumber_air_minum || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Sumber Penerangan:</strong></td>
                            <td>${data.attributes.kd_sumber_penerangan_utama || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Bahan Bakar untuk Memasak:</strong></td>
                            <td>${data.attributes.kd_bahan_bakar_memasak || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Tempat Pembuangan Akhir Tinja:</strong></td>
                            <td>${data.attributes.kd_pembuangan_akhir_tinja  || 'N/A'}</td>
                        </tr>
                    </table>
                `;
            }

            $('#filter-tahun').on('change', function() {
                dtks.ajax.reload();
                data_grafik = [];
                tampilChart('bar', 'barChart', generateChartData(data_grafik, 'label', 'Statistik Papan'));
            });

            $('#cetak').on('click', function() {
                let baseUrl = "{{ url('satu-data/dtks/cetak') }}";
                let params = dtks.ajax.params(); // Get DataTables params
                let queryString = new URLSearchParams(params).toString(); // Convert params to query string
                window.open(`${baseUrl}?${queryString}`, '_blank'); // Open the URL with appended query
            });

            // Combine data by aggregating values for duplicate labels
            function combineData(data) {
                const result = {};
                data.forEach(item => {
                    if (item.label in result) {
                        result[item.label] += item.value;
                    } else {
                        result[item.label] = item.value;
                    }
                });
                return Object.entries(result).map(([label, value]) => ({ label, value }));
            }
        })
    </script>
@endsection
