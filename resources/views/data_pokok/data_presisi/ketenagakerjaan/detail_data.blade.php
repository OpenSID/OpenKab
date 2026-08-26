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
                    <table class="table table-striped" id="detail-ketenagakerjaan">
                        <thead>
                            <tr>
                                <th>NO</th>
                                <th>NIK</th>
                                <th>NOMOR KK</th>
                                <th>NAMA</th>                                
                                <th>PEKERJAAN</th>
                                <th>TEMPAT KERJA</th>
                                <th>FREKWENSI MENGIKUTI PELATIHAN SETAHUN</th>
                                <th>JENIS PELATIHAN YANG DIIKUTI SETAHUN</th>
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
        var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/data-presisi/ketenagakerjaan' }}");
        // Cache selector di luar DataTables initialization
        const $filterTahun = $('#filter-tahun');
        const $detailPendidikan = $('#detail-ketenagakerjaan');
        var ketenagakerjaan = $detailPendidikan.DataTable({
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
                        "filter[tahun]": $filterTahun.val(),
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
                error: function(xhr, error, code) {
                    console.error('DataTables AJAX error:', {xhr, error, code});
                    alert('Gagal memuat data. Silakan refresh halaman atau hubungi administrator.');
                }
            },
            columnDefs: [{
                targets: '_all',
                className: 'text-nowrap',
            }, {
                targets: [0, 1, 2, 3, 4],
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
                    defaultContent: 'xxxxxxxxxxxx',
                },
                {
                    data: 'attributes.no_kk',
                    orderable: false,
                    defaultContent: 'xxxxxxxxxxxx',
                },
                {
                    data: 'attributes.nama',
                    orderable: false,
                },                
                {
                    data: 'attributes.jenis_pekerjaan',
                    orderable: false
                },
                {
                    data: 'attributes.tempat_kerja',
                    orderable: false
                },
                {
                    data: 'attributes.frekwensi_mengikuti_pelatihan_setahun',
                    orderable: false
                },
                {
                    data: 'attributes.jenis_pelatihan_diikuti_setahun',
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
        });
        ketenagakerjaan.on('draw.dt', function() {
            var PageInfo = $detailPendidikan.DataTable().page.info();
            ketenagakerjaan.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        // Event listener for year filter change
        $filterTahun.on('change', function() {
            ketenagakerjaan.ajax.reload();
        });
    });
</script>
@endsection
