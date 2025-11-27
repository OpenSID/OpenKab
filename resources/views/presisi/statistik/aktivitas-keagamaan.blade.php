@extends('layouts.index')

@section('plugins.chart', true)

@section('title', 'Data Statistik')

@section('content_header')
    <h1>Data Statistik {{ $judul }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row" id="tampilkan-statistik">
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistik {{ $judul }}</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-pills flex-column" id="daftar-statistik">
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <h3 id="title-block"></h3>
                    </div>
                    <div class="row">
                        <div class="col-auto">
                            <a class="btn btn-sm btn-secondary" data-toggle="collapse" href="#collapse-filter"
                                role="button" aria-expanded="true" aria-controls="collapse-filter">
                                <i class="fas fa-filter"></i>
                            </a>
                        </div>

                        <div class="col-md-2">
                            <button type="button" id="export-excel" class="btn btn-info btn-block btn-sm">
                                <i class="fa fa-file-excel"></i>
                                Excel
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button id="btn-grafik" class="btn btn-sm btn-success btn-block btn-sm" data-toggle="collapse"
                                href="#grafik-statistik" role="button" aria-expanded="false"
                                aria-controls="grafik-statistik">
                                <i class="fas fa-chart-bar"></i> Grafik
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button id="btn-pie" class="btn btn-sm btn-warning btn-block btn-sm" data-toggle="collapse"
                                href="#pie-statistik" role="button" aria-expanded="false" aria-controls="pie-statistik">
                                <i class="fas fa-chart-pie"></i> Chart
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="grafik-statistik" class="collapse">
                                <div class="chart" id="grafik">
                                    <canvas id="barChart"></canvas>
                                </div>
                                <hr class="hr-chart">
                            </div>

                            <div id="pie-statistik" class="collapse">
                                <div class="chart" id="pie">
                                    <canvas id="donutChart"></canvas>
                                </div>
                                <hr class="hr-chart">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped cell-border" id="tabel-data">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nilai</th>
                                    <th>Jumlah</th>
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
    @include('statistik.chart')
    <script nonce="{{ csp_nonce() }}">
        let data_grafik = [];
        let nama_desa = `{{ session('desa.nama_desa') }}`;
        let kategori = `{{ strtolower($judul) }}`;
        let default_id = null;
        document.addEventListener("DOMContentLoaded", function(event) {

            const header = @include('layouts.components.header_bearer_api_gabungan');

            var baseUrl = {!! json_encode(config('app.databaseGabunganUrl')) !!} + "/api/v1";

            var urlKategoriStatistik = new URL(`${baseUrl}/data-presisi/agama/kategori-statistik`);

            $.ajax({
                url: urlKategoriStatistik.href,
                headers: header,
                method: 'get',
                success: function(response) {
                    var daftarKategoriStatistik = response.data[0]['attributes']
                    var html = ''

                    Object.keys(daftarKategoriStatistik).forEach(function(index) {
                        var id = index;
                        var nama = daftarKategoriStatistik[index];

                        html += `
                        <li class="nav-item pilih-kategori">
                            <a data-id="${id}" data-nama="${nama}" class="nav-link ${id == default_id ? 'active' : ''}" href="#">
                                <i class="fas fa-angle-right"></i> ${nama}
                            </a>
                        </li>
                    `
                    });

                    $('#daftar-statistik').html(html)
                    $('#daftar-statistik').find('.pilih-kategori > a').eq(0).click();
                }
            });

            $('#daftar-statistik').on('mouseenter', '.pilih-kategori > a', function() {
                $(this).css('cursor', 'pointer')
            });

            // Helper function to create Excel export caption
            function createExportCaption(categoryName, options = {}) {
                const {
                    includeDate = true,
                        includeLocation = true,
                        customTitle = null,
                } = options;

                var caption = {
                    title: customTitle || `Data Statistik ${categoryName}`,
                    period: '',
                    date: '',
                    location: ''
                };


                // Add export date
                if (includeDate) {
                    var now = new Date();
                    caption.date =
                        `Diekspor pada: ${now.toLocaleDateString('id-ID')} ${now.toLocaleTimeString('id-ID')}`;
                }

                // Add location info
                if (includeLocation) {
                    caption.location =
                        `Kabupaten: {{ session('kabupaten.nama_kabupaten') ?? 'N/A' }} | Kecamatan: {{ session('kecamatan.nama_kecamatan') ?? 'N/A' }}`;
                }

                return caption;
            }

            // Function to generate dynamic Excel export
            function exportToExcel() {
                console.log('Starting Excel export...');

                // Check if table has data
                var tableRows = $('#tabel-data tbody tr').length;
                console.log('Table rows count:', tableRows);

                if (tableRows === 0) {
                    alert('Tidak ada data untuk diekspor. Silakan pilih kategori terlebih dahulu.');
                    return false;
                }

                // Get current active category
                var activeCategory = $('#daftar-statistik .active');
                var categoryName = activeCategory.data('nama') || 'Statistik';
                var tahun = $("#tahun").val();
                var bulan = $("#bulan").val();

                // Generate dynamic filename
                var filename = `Statistik_${categoryName}_${nama_desa}`;

                // Clean filename - remove special characters
                filename = filename.replace(/[^a-zA-Z0-9_-]/g, '_');

                // Generate sheet name (max 31 chars for Excel)
                var sheetName = categoryName.replace(/[^a-zA-Z0-9]/g, '_').substring(0, 31) || 'Statistik';

                // Create export caption using helper function
                // Anda bisa mengkustomisasi caption dengan mengubah opsi di bawah ini:
                var caption = createExportCaption(categoryName, {
                    includeDate: true, // Tampilkan tanggal export
                    includeLocation: false, // Tampilkan info kabupaten/kecamatan
                    includePeriod: true, // Tampilkan info tahun/bulan
                    customTitle: `Laporan Statistik {{ $judul }} Kategori ${categoryName}`, // Custom judul utama
                });

                // Create a clone of the table for export
                var $originalTable = $('#tabel-data');
                var $exportTable = $originalTable.clone();
                $exportTable.attr('id', 'export-table');

                // Add title rows before the header
                var titleRows = '';
                titleRows +=
                    `<tr><td colspan="3" style="text-align: center; font-weight: bold; font-size: 18px; background-color: #2c3e50; color: white; padding: 10px;">${caption.title}</td></tr>`;

                if (caption.period) {
                    titleRows +=
                        `<tr><td colspan="3" style="text-align: center; font-size: 12px; background-color: #ecf0f1; padding: 5px;">${caption.period}</td></tr>`;
                }

                if (caption.date) {
                    titleRows +=
                        `<tr><td colspan="3" style="text-align: center; font-size: 10px; background-color: #bdc3c7; padding: 3px;">${caption.date}</td></tr>`;
                }

                // Add empty row for spacing
                titleRows += '<tr><td colspan="3" style="height: 15px; background-color: white;"></td></tr>';

                // Insert title rows into the table
                $exportTable.find('thead').prepend(titleRows);

                // Add footer information
                var footerRows = '';
                footerRows += '<tr><td colspan="3" style="height: 15px; background-color: white;"></td></tr>';
                footerRows +=
                    `<tr><td colspan="3" style="text-align: left; font-size: 10px; background-color: #ecf0f1; padding: 5px;">Catatan: Data statistik ini dihasilkan dari sistem informasi desa</td></tr>`;

                if (caption.location) {
                    footerRows +=
                        `<tr><td colspan="3" style="text-align: left; font-size: 10px; background-color: #ecf0f1; padding: 5px;">${caption.location}</td></tr>`;
                }

                // Add footer to table
                $exportTable.find('tbody').append(footerRows);

                // Temporarily add the export table to DOM (hidden)
                $exportTable.css('display', 'none');
                $('body').append($exportTable);

                // Create temporary anchor with proper attributes
                var tempAnchor = document.createElement('a');
                tempAnchor.download = filename + '.xls';
                tempAnchor.href = '#';
                tempAnchor.setAttribute('download', filename + '.xls');

                // Add to DOM temporarily
                document.body.appendChild(tempAnchor);

                try {
                    // Call ExcellentExport directly using the export table
                    console.log('Calling ExcellentExport.excel...');
                    var result = ExcellentExport.excel(tempAnchor, 'export-table', sheetName);
                    console.log('Export result:', result);

                    if (result) {
                        tempAnchor.click(); // Trigger download
                        // Optional: Show success message
                        console.log('File Excel berhasil diunduh: ' + filename + '.xls');
                    } else {
                        console.error('Excel export failed - no result returned');
                        alert('Gagal mengunduh file Excel. Silakan coba lagi.');
                    }
                } catch (error) {
                    console.error('Excel export error:', error);
                    alert('Terjadi kesalahan saat mengunduh Excel: ' + error.message);
                } finally {
                    // Clean up
                    document.body.removeChild(tempAnchor);
                    $('#export-table').remove(); // Remove the temporary table
                }

                return result;
            }

            $('#export-excel').on('click', function() {
                console.log('Export button clicked');
                exportToExcel();
            });

            $('#btn-grafik').on('click', function() {
                $("#pie-statistik").collapse('hide');
            });

            $('#btn-pie').on('click', function() {
                $("#grafik-statistik").collapse('hide')
            });

            $('#daftar-statistik').on('click', '.pilih-kategori > a', function() {
                var id = $(this).data('id')

                $('.pilih-kategori > a').removeClass('active')
                $(this).addClass('active')
                $('#title-block').html($(this).text())
                urlStatistik.searchParams.set('kategori', id);

                statistik.ajax.url(urlStatistik.href, {
                    headers: header,
                }).load();
            });
            const urlDetailLink = `{{ $detailLink }}?kategori=${kategori}`;
            var urlStatistik = new URL(`${baseUrl}/data-presisi/agama/statistik`);
            urlStatistik.searchParams.set('kategori', default_id);
            urlStatistik.searchParams.set("kode_kabupaten", "{{ session('kabupaten.kode_kabupaten') ?? '' }}");
            urlStatistik.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");

            const desaId = parseInt("{{ session('desa.id') ?? '0' }}", 10);
            urlStatistik.searchParams.set("config_desa", isNaN(desaId) ? 0 : desaId);

            var statistik = $('#tabel-data').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ordering: false,
                searching: false,
                deferLoading: 0,
                paging: false,
                info: false,
                ajax: {
                    url: urlStatistik.href,
                    headers: header,
                    method: 'get',
                    data: function(row) {
                        return {

                        };
                    },
                    dataSrc: function(json) {
                        if (json.data && json.data.length > 0) {
                            data_grafik = [];
                            json.data.forEach(function(item, index) {
                                data_grafik.push({
                                    nama: item.attributes.nilai,
                                    jumlah: item.attributes.jumlah
                                })
                            })

                            grafikPie()

                            return json.data;
                        }

                        return [];
                    },
                },
                columnDefs: [{
                        targets: '_all',
                        className: 'text-nowrap',
                    },
                    {
                        targets: [2],
                        className: 'dt-body-right',
                    },
                ],
                columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }
                }, {
                    data: function(data) {
                        const nilai = data.attributes?.nilai || data.id || '';

                        if (nilai !== 'JUMLAH' && nilai !== 'BELUM MENGISI' && nilai !==
                            'TOTAL') {
                            let judul = $('.pilih-kategori > a.active').text() + ' : ' + nilai;
                            let urlDetail = new URL(urlDetailLink);
                            urlDetail.searchParams.set('filter[nilai]', nilai);
                            urlDetail.searchParams.set('judul', judul);
                            urlDetail.searchParams.set('nama', nilai);
                            urlDetail.searchParams.set('tipe', $('.pilih-kategori > a.active')
                                .text().trim());
                            urlDetail.searchParams.set('chart-view', true);

                            return `<a target="_blank" href="${urlDetail.href}">${nilai}</a>`
                        }

                        return nilai;
                    },
                }, {
                    data: function(data) {
                        const nilai = data.attributes?.nilai || data.id || '';
                        const jumlah = data.attributes?.jumlah || 0;

                        if (nilai !== 'JUMLAH' && nilai !== 'BELUM MENGISI' && nilai !==
                            'TOTAL') {
                            let judul = $('.pilih-kategori > a.active').text() + ' : ' + nilai;
                            let urlDetail = new URL(urlDetailLink);
                            urlDetail.searchParams.set('filter[nilai]', nilai);
                            urlDetail.searchParams.set('judul', judul);
                            return `<a target="_blank" href="${urlDetail.href}">${jumlah}</a>`
                        }

                        return jumlah;
                    },
                }]
            });

            statistik.on('draw.dt', function() {
                var dataTable = $('#tabel-data').DataTable();
                var pageInfo = dataTable.page.info();
                var recordsTotal = dataTable.data().count();

                statistik.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + pageInfo.start;
                });
            });

            $('#filter').on('click', function(e) {
                statistik.draw();
            });

            $(document).on('click', '#reset', function(e) {
                e.preventDefault();
                statistik.ajax.reload();
            });
        });
    </script>
@endsection
@push('css')
    <style nonce="{{ csp_nonce() }}">
        #barChart {
            min-height: 250px;
            height: 250px;
            max-height: 250px;
            max-width: 100%;
        }

        #donutChart {
            min-height: 250px;
            height: 250px;
            max-height: 250px;
            max-width: 100%;
        }

        hr.hr-chart {
            margin-right: -20px;
            margin-left: -20px;
        }

        a[target="_blank"] {
            color: blue;
        }
    </style>
@endpush
