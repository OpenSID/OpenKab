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
                        <div class="col-sm-3">
                            <button id="cetak" type="button" class="btn btn-primary btn-sm mt-4" data-url="">
                                <i class="fa fa-print"></i> Cetak
                            </button>
                            <button type="button" id="export-excel" class="btn btn-info btn-sm mt-4">
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
                                    <th>Uraian</th>
                                    <th>Data Lengkap</th>
                                    <th>Lengkap Sebagian</th>
                                    <th>Tidak Lengkap</th>
                                    <th>Total Data</th>
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
            var url = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/data-presisi/laporan-perdesa' }}");

            var laporanTable = $('#laporanTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
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
                            // "filter[config_id]": "{{ session('desa.id') ?? '' }}",
                            "kode_kabupaten": "{{ session('kabupaten.kode_kabupaten') ?? '' }}",
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
                        data: 'uraian',
                    },
                    {
                        data: 'lengkap',
                        render: $.fn.dataTable.render.number('.', ',', 0, '')
                    },
                    {
                        data: 'sebagian',
                        render: $.fn.dataTable.render.number('.', ',', 0, '')
                    },
                    {
                        data: 'tidak_lengkap',
                        render: $.fn.dataTable.render.number('.', ',', 0, '')
                    },
                    {
                        data: 'total',
                        render: $.fn.dataTable.render.number('.', ',', 0, '')
                    },
                ]
            });

            $('#cetak').on('click', function() {
                let baseUrl = "{{ route('laporan.data-presisi.cetak-perdesa') }}";

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

                var namaDesa = '{{ session('desa.nama_desa') }}';
                var filename = 'Laporan_Data_Presisi_Per_Desa';
                if (namaDesa) {
                    filename += '_' + namaDesa.trim().replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_-]/g, '_');
                }
                var sheetName = 'Data_Presisi_Per_Desa';

                // Create a clone of the table for export
                var $originalTable = $('#laporanTable');
                var $exportTable = $originalTable.clone();
                $exportTable.attr('id', 'export-table');

                // Add title rows
                var titleRows =
                    '<tr><td colspan="6" style="text-align: center; font-weight: bold; font-size: 18px; background-color: #2c3e50; color: white; padding: 10px;">Laporan Data Presisi Per Desa</td></tr>';
                if (namaDesa) {
                    titleRows +=
                        '<tr><td colspan="6" style="text-align: center; font-size: 14px; background-color: #34495e; color: white; padding: 8px;">Desa: ' +
                        namaDesa + '</td></tr>';
                }
                titleRows +=
                    '<tr><td colspan="6" style="text-align: center; font-size: 10px; background-color: #bdc3c7; padding: 3px;">Tanggal Ekspor: ' +
                    new Date().toLocaleDateString('id-ID') + '</td></tr>';
                titleRows +=
                    '<tr><td colspan="6" style="height: 15px; background-color: white;"></td></tr>';

                $exportTable.find('thead').prepend(titleRows);

                // Add footer
                var footerRows =
                    '<tr><td colspan="6" style="height: 15px; background-color: white;"></td></tr>';
                footerRows +=
                    '<tr><td colspan="6" style="text-align: left; font-size: 10px; background-color: #ecf0f1; padding: 5px;">Catatan: Data ini dihasilkan dari sistem informasi desa</td></tr>';
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
