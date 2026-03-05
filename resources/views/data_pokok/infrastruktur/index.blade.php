@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Infrastruktur')

@section('content_header')
<h1>{{ $title }}</h1>
@stop

@section('content')
@include('partials.breadcrumbs')
<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Statistik Kondisi Transportasi</div>
            <div class="card-body">
                <div class="chart" id="grafik">
                    <canvas id="kondisiChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">Statistik Sanitasi</div>
            <div class="card-body">
                <div class="chart">
                    <canvas id="sanitasiChart"></canvas>
                </div>
                <hr>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <div class="float-left">Data Sarana dan Prasarana</div>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <x-filter-tahun />
                    <div class="col-auto">
                        <x-print-button :print-url="url('data-pokok/infrastruktur/cetak')" table-id="infrastruktur" :filter="[]" />
                    </div>
                    <x-excel-download-button :download-url="config('app.databaseGabunganUrl') . '/api/v1/infrastruktur/download'" table-id="infrastruktur" filename="data_infrastruktur" />
                </div>
                <div class="table-responsive">
                    <table class="table table-striped" id="infrastruktur">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Jenis Sarana/Prasarana</th>
                                <th>Kondisi Baik</th>
                                <th>Kondisi Rusak</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
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
@include('data_pokok.infrastruktur.grafik')
<script nonce="{{ csp_nonce() }}">
    let dataGrafik = [];
    document.addEventListener("DOMContentLoaded", function(event) {
        const header = @include('layouts.components.header_bearer_api_gabungan');
        var url = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/infrastruktur' }}");
        url.searchParams.set("kode_kabupaten", "{{ session('kabupaten.kode_kabupaten') ?? '' }}");
        url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
        url.searchParams.set("config_desa", "{{ session('desa.id') ?? '' }}");
        var infrastruktur = $('#infrastruktur').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: false,
            searching: false,
            paging: false,
            info: false,
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
                        "filter[kode_desa]": $("#kode_desa").val(),
                        "tahun": $('#filter-tahun').val()
                    };
                },
                dataSrc: function(json) {
                    if (json.length > 0) {
                        const data = json || [];
                        const findVal = (name, field = 'jumlah') => (data.find(i => i.jenis_sarana === name)?.[field]) ?? 0;

                        const jalanBaik = findVal('Jalan Raya Aspal', 'kondisi_baik');
                        const jalanBuruk = findVal('Jalan Raya Aspal', 'kondisi_rusak');
                        const jembatanBaik = findVal('Jembatan Besi Beton', 'kondisi_baik');
                        const jembatanBuruk = findVal('Jembatan Besi Beton', 'kondisi_rusak');
                        const sumurResapan = findVal('Sumur Resapan', 'jumlah');
                        const mckUmum = findVal('MCK Umum', 'jumlah');

                        dataGrafik = {jalanBaik, jalanBuruk, jembatanBaik, jembatanBuruk, sumurResapan, mckUmum};
                        grafik(dataGrafik);                        
                    }
                    return json;
                },
            },
            columnDefs: [{
                targets: '_all',
                className: 'text-nowrap',
            }, ],
            columns: [{
                    data: "kategori",
                    name: "kategori",
                    orderable: false,
                    defaultContent: '-'
                },
                {
                    data: "jenis_sarana",
                    name: "jenis_sarana",
                },
                {
                    data: "kondisi_baik",
                    name: "kondisi_baik",
                    orderable: false
                },
                {
                    data: "kondisi_rusak",
                    name: "kondisi_rusak",
                    orderable: false
                },
                {
                    data: "jumlah",
                    name: "jumlah",
                    orderable: false
                },
                {
                    data: "satuan",
                    name: "satuan",
                    orderable: false
                },
            ],
        })
        $('#filter-tahun').change(function() {
            infrastruktur.draw()
        })
    })
</script>
@endsection
@include('data_pokok.infrastruktur.style')