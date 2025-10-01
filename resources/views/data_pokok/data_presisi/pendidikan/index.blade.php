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
                        <table class="table table-striped" id="table-pendidikan">
                            <thead>
                                <tr>
                                    <th>Aksi</th>
                                    <th>#</th>
                                    <th>NIK</th>
                                    <th>Nama Kepala Keluarga</th>
                                    <th>Jumlah Anggota RTM</th>
                                    <th>Keikutsertaan KIP</th>
                                    <th>Jenis Pendidikan Kesetaraan yang diikuti </th>
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
    @include('data_pokok.data_presisi.pendidikan.chart')
    <script nonce="{{ csp_nonce() }}">
        let data_grafik = [];
        document.addEventListener("DOMContentLoaded", function(event) {
            const header = @include('layouts.components.header_bearer_api_gabungan');
            var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/pendidikan/rtm' }}");
            url.searchParams.set("kode_kabupaten", "{{ session('kabupaten.kode_kabupaten') ?? '' }}");
            url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
            url.searchParams.set("kode_desa", "{{ session('desa.id') ?? '' }}");
            var dtks = $('#table-pendidikan').DataTable({
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
                            const _url =  "{{ route('data-pokok.data-presisi-pendidikan.detail', ['data' => '__DATA__']) }}".replace('__DATA__', jsonData)
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
                        data: "attributes.keikutsertaan_kip",
                        render: (data) => data || 'N/A',
                    },
                    {
                        data: "attributes.jenis_pendidikan_kesetaraan_yg_diikuti",
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
                            <td><strong>Tingkat pendidikan yang di tamatkan:</strong></td>
                            <td>${data.attributes.pendidikan_dalam_kk || 'N/A'}</td>
                        </tr>
                        <tr>
                            <td><strong>Jenjang pendidikan yang di tamatkan:</strong></td>
                            <td>${data.attributes.pendidikan_sedang_ditempuh || 'N/A'}</td>
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
                dtks.ajax.reload();
                data_grafik = [];
                grafikPie();
            });
            
            $('#cetak').on('click', function() {
                let baseUrl = "{{ route('data-pokok.data-presisi-pendidikan.cetak') }}";
                let params = dtks.ajax.params(); // Get DataTables params
                let queryString = new URLSearchParams(params).toString(); // Convert params to query string
                window.open(`${baseUrl}?${queryString}`, '_blank'); // Open the URL with appended query
            });
        })
    </script>
@endsection