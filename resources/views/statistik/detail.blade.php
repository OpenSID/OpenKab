@extends('layouts.index')
@include('layouts.components.select2_tahun', [
    'url' => config('app.databaseGabunganUrl').'/api/v1/statistik/' . strtolower($judul) . '/tahun',
])

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/progressive-image/progressive-image.css') }}">
@endpush

@section('plugins.chart', true)

@section('title', 'Data Statistik')

@section('content_header')
    <h1 id="judul">Data Statistik {{ $judul }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row" id="tampilkan-statistik">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="float-left">
                        <div class="btn-group">
                            <a href="{{ route('statistik.rtm') }}" class="btn btn-sm btn-block btn-secondary"><i
                                    class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped cell-border" id="tabel-data">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th class="dt-head-center">Foto</th>
                                    <th class="dt-head-center">Nomor Rumah Tangga</th>
                                    <th class="dt-head-center">Kepala Rumah Tangga</th>
                                    <th class="dt-head-center">NIK</th>
                                    <th class="dt-head-center">Jumlah KK</th>
                                    <th class="dt-head-center">Jumlah Anggota</th>
                                    <th class="dt-head-center">Alamat</th>
                                    <th class="dt-head-center">Tanggal Terdaftar</th>
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

@push('js')
    <script src="{{ asset('assets/progressive-image/progressive-image.js') }}"></script>
@endpush
@section('js')
    @include('statistik.chart')
    <script nonce="{{ csp_nonce() }}"  >
    let data_grafik = [];
    let nama_desa = `{{ session('desa.nama_desa') }}`;
    let kategori = `{{ strtolower($judul) }}`;
    let default_id = `{{ $default_kategori }}`;
    document.addEventListener("DOMContentLoaded", function(event) {

        const header = @include('layouts.components.header_bearer_api_gabungan');

        var baseUrl = {!! json_encode(config('app.databaseGabunganUrl')) !!} + "/api/v1";

        var urlStatistik = new URL(`${baseUrl}/statistik/${kategori}`);
        urlStatistik.searchParams.set('filter[id]', default_id);
        urlStatistik.searchParams.set('nomor', `{{ $nomor }}`);
        urlStatistik.searchParams.set('sex', `{{ $sex }}`);

        var statistik = $('#tabel-data').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: false,
            searching: true,
            paging: true,
            info: true,
            ajax: {
                url: urlStatistik.href,
                headers: header,
                method: 'get',
                data: function(row) {
                        return {
                            "page[size]": row.length,
                            "filter[search]": row.search.value,
                            "page[number]": (row.start / row.length) + 1,
                        };
                    },
                dataSrc: function(json) {
                    if (json.data.length > 0) {
                        if(json.judul){
                            $('#judul').text(json.judul);
                        }

                        json.recordsTotal = json.meta.pagination.total
                        json.recordsFiltered = json.meta.pagination.total

                        return json.data
                    }

                    return false;
                },
            },
            columnDefs: [{
                    targets: '_all',
                    className: 'text-nowrap',
                },
                {
                    targets: [],
                    orderable: false,
                    searchable: false,
                },
            ],
            columns: [
                {
                    data: null,
                }, 
                {
                    data: function(data) {
                            let hrefTag = data.attributes.urlFoto ? 'href=' + data.attributes
                                .urlFoto : `href="{{ asset('assets/img/avatar.png') }}"`;
                            return `<a ${hrefTag} class="progressive replace kecil">
                                    <img class="preview" loading="lazy" src="{{ asset('assets/img/avatar.png') }}" alt="Foto Penduduk"/>
                                </a>`
                        }
                },
                {
                    data: "attributes.kepala_keluarga.id_rtm"
                },
                {
                    data: "attributes.kepala_keluarga.nama"
                },
                {
                    data: "attributes.kepala_keluarga.nik"
                },
                {
                    data: "attributes.jumlah_kk"
                },
                {
                    data: "attributes.anggota_count"
                },
                {
                    data: "attributes.kepala_keluarga.alamat_wilayah"
                },
                {
                    data: "attributes.tgl_daftar"
                },
            ]
        });

        statistik.on('draw.dt', function() {
                var PageInfo = $('#tabel-data').DataTable().page.info();
                statistik.column(0, {
                    page: 'current'
                }).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1 + PageInfo.start;
                });
            });

    });

    </script>
@endsection
@push('css')
    <style nonce="{{ csp_nonce() }}" >
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
    </style>
@endpush
