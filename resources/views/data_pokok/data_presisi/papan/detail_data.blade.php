@extends('layouts.index')

@section('title', $title)

@section('content_header')
<h1>{{ html_entity_decode($title) }}</h1>
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
                    <table class="table table-striped" id="detail-papan">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>NIK</th>
                                <th>NOMOR KK</th>
                                <th>NAMA</th>
                                <th>STATUS KEPEMILIKAN</th>
                                <th>LUAS LANTAI (M²)</th>
                                <th>JENIS LANTAI</th>
                                <th>JENIS DINDING</th>
                                <th>SUMBER AIR MINUM</th>
                                <th>SUMBER PENERANGAN</th>
                                <th>DAYA TERPASANG</th>
                                <th>DAYA TERPASANG 2</th>
                                <th>DAYA TERPASANG 3</th>
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
        var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/papan' }}");
        var papan = $('#detail-papan').DataTable({
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
                    data: "attributes.kd_stat_bangunan_tinggal",
                    defaultContent:  'N/A',
                    orderable: false,
                },
                {
                    data: "attributes.luas_lantai",
                    defaultContent:  'N/A',
                    orderable: false,
                },
                {
                    data: "attributes.kd_jenis_lantai_terluas",
                    defaultContent:  'N/A',
                    orderable: false,
                },
                {
                    data: "attributes.kd_jenis_dinding",                    
                    defaultContent:  'N/A',
                    orderable: false,
                },
                {
                    data: "attributes.kd_sumber_air_minum",                    
                    defaultContent:  'N/A',
                    orderable: false,
                },
                {
                    data: "attributes.kd_sumber_penerangan_utama",
                    defaultContent:  'N/A',
                    orderable: false,
                },
                {
                    data: "attributes.kd_daya_terpasang",
                    defaultContent:  'N/A',
                    orderable: false,
                },
                {
                    data: "attributes.kd_daya_terpasang2",
                    defaultContent:  'N/A',
                    orderable: false,
                },
                {
                    data: "attributes.kd_daya_terpasang3",
                    defaultContent:  'N/A',
                    orderable: false,
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
        });
        papan.on('draw.dt', function() {
            var PageInfo = $('#detail-papan').DataTable().page.info();
            papan.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        // Event listener for year filter change
        $('#filter-tahun').on('change', function() {
            papan.ajax.reload();
        });
    });
</script>
@endsection