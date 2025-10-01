@extends('layouts.index')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
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
                        <table class="table table-striped" id="table-pangan">
                            <thead>
                                <tr>
                                    <th>Aksi</th>
                                    <th>#</th>
                                    <th>NIK</th>
                                    <th>Nama Kepala Keluarga</th>
                                    <th>Jumlah Anggota RTM</th>
                                    <th>Jenis Lahan</th>
                                    <th>Luas Lahan</th>
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
    @include('data_pokok.data_presisi.pangan.chart')
    <script nonce="{{ csp_nonce() }}">
        let data_grafik = [];
        document.addEventListener("DOMContentLoaded", function(event) {
            const header = @include('layouts.components.header_bearer_api_gabungan');
            var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/pangan/rtm' }}");
            url.searchParams.set("kode_kabupaten", "{{ session('kabupaten.kode_kabupaten') ?? '' }}");
            url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
            url.searchParams.set("kode_desa", "{{ session('desa.id') ?? '' }}");
            var dtks = $('#table-pangan').DataTable({
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
                        };
                    },
                    dataSrc: function(json) {
                        if (json.data.length > 0) {
                        json.recordsTotal = json.meta.pagination.total
                        json.recordsFiltered = json.meta.pagination.total
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
                    },
                ],
                columns: [
                    {
                        data: function(data) {
                            let d = data.attributes
                            let obj = {
                                'rtm_id' : data.id,
                                'no_kartu_rumah': d.no_kk,
                                'nama_kepala_keluarga': d.kepala_keluarga,
                                'alamat': d.alamat,
                                'jumlah_anggota': d.jumlah_anggota,
                                'jumlah_kk': d.jumlah_kk,
                            }
                            let jsonData = encodeURIComponent(JSON.stringify(obj));
                            const _url =  "{{ route('data-pokok.data-presisi-pangan.detail', ['data' => '__DATA__']) }}".replace('__DATA__', jsonData)
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
                        data: "attributes.jenis_lahan",
                        render: (data) => data || 'N/A',
                    },
                    {
                        data: "attributes.luas_lahan",
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
                            <td><strong>Luas tanam:</strong></td>
                            <td>${data.attributes.luas_tanam || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Status lahan:</strong></td>
                            <td>${data.attributes.status_lahan || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Komoditas utama tanaman pangan:</strong></td>
                            <td>${data.attributes.komoditi_utama_tanaman_pangan || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Komoditas tanaman pangan lainnya:</strong></td>
                            <td>${data.attributes.komoditi_tanaman_pangan_lainnya || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Jumlah berdasarkan jenis komoditi:</strong></td>
                            <td>${data.attributes.jumlah_berdasarkan_jenis_komoditi || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Usia komoditi:</strong></td>
                            <td>${data.attributes.usia_komoditi || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Jenis peternakan:</strong></td>
                            <td>${data.attributes.jenis_peternakan || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Jumlah populasi:</strong></td>
                            <td>${data.attributes.jumlah_populasi || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Jenis perikanan:</strong></td>
                            <td>${data.attributes.jenis_perikanan || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Frekwensi makanan perhari:</strong></td>
                            <td>${data.attributes.frekwensi_makanan_perhari || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Frekwensi konsumsi sayur perhari:</strong></td>
                            <td>${data.attributes.frekwensi_konsumsi_sayur_perhari || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Frekwensi konsumsi buah perhari:</strong></td>
                            <td>${data.attributes.frekwensi_konsumsi_buah_perhari || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Frekwensi konsumsi daging perhari:</strong></td>
                            <td>${data.attributes.frekwensi_konsumsi_daging_perhari || 'N/A'}</td>
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
                            <td><strong>Tanggal terdaftar:</strong></td>
                            <td>${data.attributes.tgl_daftar || 'N/A'}</td>
                        </tr>
                    </table>
                `;
            }
            // Event listener for year filter change
            $('#filter-tahun').on('change', function() {
                dtks.ajax.reload();
                data_grafik = [];
                grafikPie();
            });
            
            $('#cetak').on('click', function() {
                let baseUrl = "{{ route('data-pokok.data-presisi-pangan.cetak') }}";
                let params = dtks.ajax.params(); // Get DataTables params
                let queryString = new URLSearchParams(params).toString(); // Convert params to query string
                window.open(`${baseUrl}?${queryString}`, '_blank'); // Open the URL with appended query
            });
        })
    </script>
@endsection