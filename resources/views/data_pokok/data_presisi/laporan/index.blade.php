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
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-2">
                            <select id="filter-status" class="form-control form-control-sm">
                                @php
                                    $statusOptions = [
                                        0 => 'Tidak Lengkap',
                                        1 => 'Lengkap Sebagian',
                                        2 => 'Data Lengkap',
                                    ];
                                @endphp
                                @foreach ($statusOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($key == 2)>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <button id="cetak" type="button" class="btn btn-primary btn-sm" data-url="">
                                <i class="fa fa-print"></i> Cetak
                            </button>
                            <button type="button" id="export-excel" class="btn btn-info btn-sm">
                                <i class="fa fa-file-excel"></i> Excel
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="laporanTable">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Desa</th>
                                    <th>Pangan</th>
                                    <th>Sandang</th>
                                    <th>Papan</th>
                                    <th>Pendidikan</th>
                                    <th>Seni Budaya</th>
                                    <th>Kesehatan</th>
                                    <th>Keagamaan</th>
                                    <th>Jaminan Sosial</th>
                                    <th>Adat</th>
                                    <th>Ketenagakerjaan</th>
                                    <th>Jumlah Penduduk</th>
                                    <th>Jumlah Rumah Tangga</th>
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
    <script nonce="{{ csp_nonce() }}" src="{{ asset('assets/js/excellentexport.js') }}"></script>
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            const header = @include('layouts.components.header_bearer_api_gabungan');
            var url = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/data-presisi/laporan' }}");
            url.searchParams.set("kode_kabupaten", "{{ session('kabupaten.kode_kabupaten') ?? '' }}");
            url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
            url.searchParams.set("kode_desa", "{{ session('desa.id') ?? '' }}");
            console.log(url);


            $('#laporanTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: url.href,
                    type: 'GET',
                    headers: header,
                    data: function(row) {
                        // Konversi parameter DataTables ke format JSON:API
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[search]": row.search.value,
                            "filter[status_kelengkapan]": $('#filter-status').val(),
                            "kode_kecamatan": "{{ session('kecamatan.kode_kecamatan') ?? '' }}",
                            "config_desa": "{{ session('desa.id') ?? '' }}",
                            "sort": row.order[0].dir === 'desc' ? '-nama_desa' : 'nama_desa'
                        };
                    },
                    dataSrc: function(json) {
                        // Konversi response JSON:API ke format DataTables
                        json.recordsTotal = json.meta.pagination.total;
                        json.recordsFiltered = json.meta.pagination.total;

                        // Extract attributes dari data
                        return json.data.map(item => item.attributes);
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'desa',
                    },
                    {
                        data: 'pangan'
                    },
                    {
                        data: 'sandang'
                    },
                    {
                        data: 'papan'
                    },
                    {
                        data: 'pendidikan'
                    },
                    {
                        data: 'seni_budaya'
                    },
                    {
                        data: 'kesehatan'
                    },
                    {
                        data: 'keagamaan'
                    },
                    {
                        data: 'jaminan_sosial'
                    },
                    {
                        data: 'adat'
                    },
                    {
                        data: 'ketenagakerjaan'
                    },
                    {
                        data: 'jumlah_penduduk',
                        render: $.fn.dataTable.render.number('.', ',', 0, '')
                    },
                    {
                        data: 'jumlah_rumah_tangga',
                        render: $.fn.dataTable.render.number('.', ',', 0, '')
                    }
                ]
            });

            // Event listener for year filter change
            $('#filter-status').on('change', function() {
                $('#laporanTable').DataTable().ajax.reload();
            });

            $('#cetak').on('click', function() {
                let baseUrl = "{{ route('laporan.data-presisi.cetak') }}";

                let params = $('#laporanTable').DataTable().ajax.params(); // Get DataTables params
                let queryString = new URLSearchParams(params).toString(); // Convert params to query string
                window.open(`${baseUrl}?${queryString}`, '_blank'); // Open the URL with appended query
            });

            // Excel export function
            $('#export-excel').on('click', function() {
                console.log('Export button clicked');

                var tableRows = $('#laporanTable tbody tr').length;
                if (tableRows === 0 || tableRows === 1 && $('#laporanTable tbody tr').hasClass(
                        'dataTables_empty')) {
                    alert('Tidak ada data untuk diekspor.');
                    return false;
                }

                var statusFilter = $('#filter-status option:selected').text();
                var filename = 'Laporan_Data_Presisi_' + statusFilter.trim().replace(/\s+/g, '_').replace(
                    /[^a-zA-Z0-9_-]/g, '_');
                var sheetName = 'Data_Presisi';

                // Create a clone of the table for export
                var $originalTable = $('#laporanTable');
                var $exportTable = $originalTable.clone();
                $exportTable.attr('id', 'export-table');

                // Add title rows
                var titleRows =
                    '<tr><td colspan="13" style="text-align: center; font-weight: bold; font-size: 18px; background-color: #2c3e50; color: white; padding: 10px;">Laporan Data Presisi</td></tr>';
                titleRows +=
                    '<tr><td colspan="13" style="text-align: center; font-size: 12px; background-color: #ecf0f1; padding: 5px;">Status: ' +
                    statusFilter + '</td></tr>';
                titleRows +=
                    '<tr><td colspan="13" style="text-align: center; font-size: 10px; background-color: #bdc3c7; padding: 3px;">Tanggal Ekspor: ' +
                    new Date().toLocaleDateString('id-ID') + '</td></tr>';
                titleRows +=
                    '<tr><td colspan="13" style="height: 15px; background-color: white;"></td></tr>';

                $exportTable.find('thead').prepend(titleRows);

                // Add footer
                var footerRows =
                    '<tr><td colspan="13" style="height: 15px; background-color: white;"></td></tr>';
                footerRows +=
                    '<tr><td colspan="13" style="text-align: left; font-size: 10px; background-color: #ecf0f1; padding: 5px;">Catatan: Data ini dihasilkan dari sistem informasi desa</td></tr>';
                $exportTable.find('tbody').append(footerRows);

                // Temporarily add to DOM
                $exportTable.css('display', 'none');
                $('body').append($exportTable);

                // Create anchor and trigger export
                var tempAnchor = document.createElement('a');
                tempAnchor.download = filename + '.xls';
                tempAnchor.href = '#';
                document.body.appendChild(tempAnchor);

                try {
                    var result = ExcellentExport.excel(tempAnchor, 'export-table', sheetName);
                    if (result) {
                        tempAnchor.click();
                        console.log('File Excel berhasil diunduh: ' + filename + '.xls');
                    } else {
                        alert('Gagal mengunduh file Excel. Silakan coba lagi.');
                    }
                } catch (error) {
                    console.error('Excel export error:', error);
                    alert('Terjadi kesalahan saat mengunduh Excel: ' + error.message);
                } finally {
                    document.body.removeChild(tempAnchor);
                    $('#export-table').remove();
                }
            });
        })
    </script>
@endsection
