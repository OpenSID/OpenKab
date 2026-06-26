@extends('layouts.index')

@section('title', $title)

@section('content_header')
<h1>{{ $title }}</h1>
@stop

@section('content')
@include('partials.breadcrumbs')

<div class="row">
    <div class="col-lg-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <div class="row">
                    <x-filter-tahun :selectedYear="request('tahun')" />                    
                </div>
            </div>
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
        @php
            $kodeKabupaten = session('kabupaten.kode_kabupaten') ?? '';
            $kodeKecamatan = session('kecamatan.kode_kecamatan') ?? '';
            $configDesa = session('desa.id') ?? '';
        @endphp
        const kodeKabupaten = "{{ $kodeKabupaten }}";
        const kodeKecamatan = "{{ $kodeKecamatan }}";
        const configDesa = "{{ $configDesa }}";
        var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/pangan' }}");        
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
                    const data = {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[search]": row.search.value,
                        "filter[tahun]": $('#filter-tahun').val(),
                        "filter[colomn]": '{{ $colomn }}',
                        "kode_kabupaten": kodeKabupaten,
                        "kode_kecamatan": kodeKecamatan,
                        "config_desa": configDesa,
                        "sort": (row.order[0]?.dir === "asc" ? "" : "-") + row.columns[row.order[0]?.column]?.name,
                    };
                    
                    return data;
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
            columns: [{
                    data: null,
                    orderable: false,
                    render: function(data, type, row, meta) {
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
                {
                    data: 'attributes.jenis_lahan',
                    orderable: false
                },
                {
                    data: 'attributes.luas_lahan',
                    orderable: false
                },
                {
                    data: 'attributes.luas_tanam',
                    orderable: false
                },
                {
                    data: 'attributes.status_lahan',
                    orderable: false
                },
                {
                    data: 'attributes.komoditi_utama_tanaman_pangan',
                    orderable: false
                },
                {
                    data: 'attributes.komoditi_tanaman_pangan_lainnya',
                    orderable: false
                },
                {
                    data: 'attributes.jumlah_berdasarkan_jenis_komoditi',
                    orderable: false
                },
                {
                    data: 'attributes.usia_komoditi',
                    orderable: false
                },
                {
                    data: 'attributes.jenis_peternakan',
                    orderable: false
                },
                {
                    data: 'attributes.jumlah_populasi',
                    orderable: false
                },
                {
                    data: 'attributes.jenis_perikanan',
                    orderable: false
                },
                {
                    data: 'attributes.frekwensi_makanan_perhari',
                    orderable: false
                },
                {
                    data: 'attributes.frekwensi_konsumsi_sayur_perhari',
                    orderable: false
                },
                {
                    data: 'attributes.frekwensi_konsumsi_buah_perhari',
                    orderable: false
                },
                {
                    data: 'attributes.frekwensi_konsumsi_daging_perhari',
                    orderable: false
                },
                {
                    data: 'attributes.longitude',
                    orderable: false
                },
                {
                    data: 'attributes.latitude',
                    orderable: false
                },
                {
                    data: 'attributes.tanggal_pengisian',
                    orderable: false
                },
                {
                    data: 'attributes.status_pengisian',
                    orderable: false
                },
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

        // Event listener for year filter change
        $('#filter-tahun').on('change', function() {
            pangan.ajax.reload();            
        });
    });
</script>
@endsection