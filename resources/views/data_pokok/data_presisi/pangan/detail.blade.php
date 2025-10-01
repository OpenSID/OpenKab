@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'DATA')

@section('content_header')
    <h1>Data {{ $data->nama_kepala_keluarga }} ({{$data->no_kartu_rumah}})</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="row">
                        <div class="col-sm-12 mb-4">
                            <a href="{{ route('data-pokok.data-presisi-pangan.index') }}" class="btn btn-social btn-info btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-arrow-circle-left"></i> Kembali Ke Daftar Data Pangan</a>
                        </div>
                    </div>
                    <div class="box-body">
                        <h5><b>Rincian Suplemen</b></h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover tabel-rincian">
                                <tbody>
                                    <tr>
                                        <td width="20%">No Kartu Rumah Tangga (KRT)</td>
                                        <td width="1%">:</td>
                                        <td>{{ $data->no_kartu_rumah }}</td>
                                    </tr>
                                    <tr>
                                        <td>Kepala Rumah Tangga</td>
                                        <td>:</td>
                                        <td>{{ $data->nama_kepala_keluarga }}</td>
                                    </tr>
                                    <tr>
                                        <td>Alamat</td>
                                        <td>:</td>
                                        <td>{{ $data->alamat }}</td>
                                    </tr>
                                    <tr>
                                        <td>Jumalah Anggota</td>
                                        <td>:</td>
                                        <td>{{ $data->jumlah_anggota }}</td>
                                    </tr>
                                    <tr>
                                        <td>Jumlah KK</td>
                                        <td>:</td>
                                        <td>{{ $data->jumlah_kk }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            <div class="card card-outline card-primary">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="detail-pangan">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>NIK</th>
                                    <th>NOMOR KK</th>
                                    <th>NAMA</th>
                                    <th>JENIS LAHAN</th>
                                    <th>LUAS LAHAN</th>
                                    <th>LUAS TANAM</th>
                                    <th>STATUS LAHAN</th>
                                    <th>KOMODITI UTAMA TANAMAN PANGAN</th>
                                    <th>KOMODITI TANAMAN PANGAN LAINNYA</th>
                                    <th>JUMLAH BERDASARKAN JENIS KOMODITI</th>
                                    <th>USIA KOMODITI</th>
                                    <th>JENIS PETERNAKAN</th>
                                    <th>JUMLAH POPULASI</th>
                                    <th>JENIS PERIKANAN</th>
                                    <th>FREKWENSI MAKANAN PERHARI</th>
                                    <th>FREKWENSI KONSUMSI SAYUR PERHARI</th>
                                    <th>FREKWENSI KONSUMSI BUAH PERHARI</th>
                                    <th>FREKWENSI KONSUMSI DAGING PERHARI</th>
                                    <th>LONGITUDE</th>
                                    <th>LATITUDE</th>
                                    <th>TANGGAL PENGISIAN</th>
                                    <th>STATUS PENGISIAN</th>
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
    <script nonce="{{ csp_nonce() }}">
    document.addEventListener("DOMContentLoaded", function(event) {
        const headers = @include('layouts.components.header_bearer_api_gabungan');
        var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/pangan' }}");
        url.searchParams.set("filter[rtm_id]", "{{ $data->rtm_id }}");
        var pangan = $('#detail-pangan').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            searchPanes: {
                viewTotal: false,
                columns: [0]
            },
            ajax: {
                url: url.href,
                headers: headers,
                method: 'get',
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[search]": row.search.value,
                        "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]?.name,
                    };
                },
                dataSrc: function(json) {
                    json.recordsTotal = json.meta.pagination.total;
                    json.recordsFiltered = json.meta.pagination.total;
                    return json.data;
                },
            },
            columnDefs: [{
                targets: '_all',
                className: 'text-nowrap',
            }, {
                targets: [0, 1, 2, 3, 4, 5],
                orderable: false,
                searchable: false,
            }],
            columns: [
                {
                    data: null,
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'attributes.nik',
                    orderable: false,
                },
                {
                    data: 'attributes.no_kk',
                    orderable: false,
                },
                {
                    data: 'attributes.nama',
                    orderable: false,
                },
                { data: 'attributes.jenis_lahan', orderable: false },
                { data: 'attributes.luas_lahan', orderable: false },
                { data: 'attributes.luas_tanam', orderable: false },
                { data: 'attributes.status_lahan', orderable: false },
                { data: 'attributes.komoditi_utama_tanaman_pangan', orderable: false },
                { data: 'attributes.komoditi_tanaman_pangan_lainnya', orderable: false },
                { data: 'attributes.jumlah_berdasarkan_jenis_komoditi', orderable: false },
                { data: 'attributes.usia_komoditi', orderable: false },
                { data: 'attributes.jenis_peternakan', orderable: false },
                { data: 'attributes.jumlah_populasi', orderable: false },
                { data: 'attributes.jenis_perikanan', orderable: false },
                { data: 'attributes.frekwensi_makanan_perhari', orderable: false },
                { data: 'attributes.frekwensi_konsumsi_sayur_perhari', orderable: false },
                { data: 'attributes.frekwensi_konsumsi_buah_perhari', orderable: false },
                { data: 'attributes.frekwensi_konsumsi_daging_perhari', orderable: false },
                { data: 'attributes.longitude', orderable: false },
                { data: 'attributes.latitude', orderable: false },
                { data: 'attributes.tanggal_pengisian', orderable: false },
                { data: 'attributes.status_pengisian', orderable: false },
            ],
            order: [
                [10, 'asc']
            ]
        });
        pangan.on('draw.dt', function() {
            var PageInfo = $('#detail-pangan').DataTable().page.info();
            pangan.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });
        $('#sex, #dusun, #rw, #rt').change(function() {
            pangan.draw();
        });
    });
    </script>
@endsection