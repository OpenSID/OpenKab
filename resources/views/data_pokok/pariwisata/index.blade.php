@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Bantuan')

@section('content_header')
<h1>{{ $title }}</h1>
@stop

@section('content')
@include('partials.breadcrumbs')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Statistik Jumlah Penginapan</div>
            <div class="card-body">
                <div>
                    <div class="chart" id="grafik">
                        <canvas id="barChart"></canvas>
                    </div>
                    <hr class="hr-chart">
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Statistik Tingkat Pemanfaatan</div>
            <div class="card-body">                
                <div>
                    <div class="chart" id="pie">
                        <canvas id="donutChart"></canvas>
                    </div>
                    <hr class="hr-chart">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <div class="float-left">{{ $title }}</div>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <x-filter-tahun />
                    <x-filter-kategori-wisata />
                    <div id="filter-sarana-wisata-container">
                        <x-filter-sarana-wisata />
                    </div>
                    <div id="filter-komoditas-wisata-container">
                        <x-filter-komoditas-wisata />
                    </div>
                    <div class="col-auto">
                        <x-print-button :print-url="url('data-pokok/pariwisata/cetak')" table-id="pariwisata" :filter="[]" />
                    </div>
                    <x-excel-download-button :download-url="config('app.databaseGabunganUrl') . '/api/v1/pariwisata/download'" table-id="pariwisata" filename="data_pariwisata" />
                </div>
                <div class="table-responsive">
                    <table class="table table-striped" id="pariwisata">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>{{ config('app.sebutanDesa') }}</th>
                                <th>Jenis Hiburan</th>
                                <th>Jumlah Penginapan</th>
                                <th>Lokasi/Tempat/Area Wisata</th>
                                <th>Keberadaan</th>
                                <th>Luas (Ha)</th>
                                <th>Tingkat Pemanfaatan</th>
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
@include('data_pokok.pariwisata.chart')
<script nonce="{{ csp_nonce() }}">
    let data_grafik = [];


    document.addEventListener("DOMContentLoaded", function(event) {

        const header = @include('layouts.components.header_bearer_api_gabungan');
        var url = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/pariwisata' }}");
        url.searchParams.set("kode_kabupaten", "{{ session('kabupaten.kode_kabupaten') ?? '' }}");
        url.searchParams.set("kode_kecamatan", "{{ session('kecamatan.kode_kecamatan') ?? '' }}");
        url.searchParams.set("config_desa", "{{ session('desa.id') ?? '' }}");

        var pariwisata = $('#pariwisata').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            searchPanes: {
                viewTotal: false,
                columns: [0]
            },
            dom: 'lrtpi',
            ajax: {
                url: url.href,
                method: 'get',
                headers: header,
                data: function(row) {
                    return {
                        "page[size]": row.length,
                        "page[number]": (row.start / row.length) + 1,
                        "filter[search]": row.search.value,
                        "filter[tahun]": $('#filter-tahun').val(),
                        "filter[kategori]": $('#filter-kategori-wisata').val(),
                        "filter[sarana-wisata]": $('#filter-sarana-wisata').val(),
                        "filter[potensi-wisata]": $('#filter-komoditas-wisata').val(),
                        "sort": "id",
                    };
                },
                dataSrc: function(json) {
                    // Set default values untuk recordsTotal dan recordsFiltered
                    json.recordsTotal = json.meta?.pagination?.total || 0;
                    json.recordsFiltered = json.meta?.pagination?.total || 0;

                    if (json.data && json.data.length > 0) {
                        data_grafik = [];
                        json.data.forEach(function(item, index) {
                            data_grafik.push(item.attributes)
                        })

                        grafikPie()
                    } else {
                        // Kosongkan data grafik jika tidak ada data
                        data_grafik = [];
                    }

                    return json.data || [];
                },
            },
            columnDefs: [{
                targets: '_all',
                className: 'text-nowrap',
            }, ],
            columns: [{
                    data: null,
                },
                {
                    data: "attributes.nama_desa",
                    name: "desa",
                    orderable: false
                },
                {
                    data: "attributes.jenis_hiburan",
                    name: "jenis_hiburan",
                    orderable: false
                },
                {
                    data: "attributes.jumlah_penginapan",
                    name: "jumlah_penginapan",
                    orderable: false
                },
                {
                    data: "attributes.lokasi_tempat_area_wisata",
                    name: "lokasi_tempat_area_wisata",
                    className: 'text-center',
                    orderable: false
                },
                {
                    data: "attributes.keberadaan",
                    name: "keberadaan",
                    orderable: false
                },
                {
                    data: "attributes.luas",
                    name: "luas",
                    orderable: false
                },
                {
                    data: "attributes.tingkat_pemanfaatan",
                    name: "tingkat_pemanfaatan",
                    orderable: false
                },
            ],
            order: [
                [0, 'asc']
            ]
        })

        pariwisata.on('draw.dt', function() {
            var PageInfo = $('#pariwisata').DataTable().page.info();
            pariwisata.column(0, {
                page: 'current'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1 + PageInfo.start;
            });
        });

        $('#filter-tahun, #filter-sarana-wisata, #filter-komoditas-wisata').change(function() {
            pariwisata.draw()
        })

        // Logika untuk menampilkan/menyembunyikan filter berdasarkan kategori
        $('#filter-kategori-wisata').change(function() {
            var kategori = $(this).val();
            
            // Sembunyikan semua filter terlebih dahulu
            $('#filter-sarana-wisata-container').hide();
            $('#filter-komoditas-wisata-container').hide();
            
            // Reset nilai filter
            $('#filter-sarana-wisata').val('');
            $('#filter-komoditas-wisata').val('');
            
            // Tampilkan filter yang sesuai dengan kategori
            if (kategori === 'sarana-wisata') {
                $('#filter-sarana-wisata-container').show();
            } else if (kategori === 'potensi-wisata') {
                $('#filter-komoditas-wisata-container').show();
            }
            
            // Refresh tabel
            pariwisata.draw();
        })

        $('#filter-sarana-wisata-container').hide();
        $('#filter-komoditas-wisata-container').hide();

    })
</script>
@endsection
