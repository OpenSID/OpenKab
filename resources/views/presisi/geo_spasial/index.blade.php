@extends('layouts.presisi.index')

@section('content_header')
@stop
@section('content')
    @include('presisi.partials.head')

    <div class="row m-1">
        @include('presisi.geo_spasial.wilayah.data-wilayah')

        <div class="col-md-12">

            <div class="card rounded-0 border-0 shadow-none">
                <!-- @include('presisi.summary') -->
                <div class="card-body">
                    @include('presisi.geo_spasial.wilayah.filter')

                    @include('presisi.geo_spasial.wilayah.peta')

                </div>
            </div>
        </div>


        @include('presisi.geo_spasial.wilayah.data-desa')



    </div>

    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Detail Informasi</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalContent">
                    <!-- Konten detail akan diisi di sini -->
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style nonce="{{ csp_nonce() }}">
        @media (min-width: 768px) {
            .col-md-4 {
                flex: 0 0 auto;
                width: 31.1% !important;
                margin: 10px !important;
            }
        }

        #map {
            height: 350px;
        }

        .geospasial-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 0;
            position: relative;
        }

        .geospasial-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .geospasial-card .detail-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            color: #007bff;
            cursor: pointer;
        }

        .geospasial-card .description {
            font-size: 14px;
            color: rgb(102, 102, 102);
            display: block;
            padding: 10px;
        }

        .geospasial-card .separator {
            height: 1px;
            background-color: #ddd;
            margin: 0 10px;
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush
@push('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script nonce="{{ csp_nonce() }}" type="text/javascript">
        document.addEventListener("DOMContentLoaded", function(event) {
            "use strict";

            const header = @include('layouts.components.header_bearer_api_gabungan');

            const position = [{{ env('LATTITUDE_MAP', -8.459556) }}, {{ env('LONGITUDE_MAP', 115.0466) }}]
            const map = L.map('map').setView(position, 13);
            const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                // maxZoom: 15,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map)

            @include('presisi.geo_spasial.layouts.peta.filter')
            @include('presisi.geo_spasial.layouts.peta.index')
            @include('presisi.geo_spasial.layouts.peta.style')
            @include('presisi.geo_spasial.layouts.wilayah.data')
            
            const headers = @include('layouts.components.header_bearer_api_gabungan');
            var urlDataWebsite = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/data-website' }}");

            $.ajax({
                url: urlDataWebsite.href,
                method: 'GET',
                dataType: 'json',
                headers: headers,
                success: function(result) {
                    let category = result.data.categoriesItems;
                    let listDesa = result.data.listDesa;
                    let listKecamatan = result.data.listKecamatan;

                    for (let index in category) {
                        $(`.kategori-item .jumlah-${index}-elm`).text(category[index]['value']);
                    }

                    let _optionKecamatan = [];
                    let _optionDesa = [];

                    for (let item in listKecamatan) {
                        _optionKecamatan.push(`<option>${item}</option>`);
                    }

                    for (let item in listDesa) {
                        _optionDesa.push(`<optgroup label='${item}'>`);
                        for (let desa in listDesa[item]) {
                            _optionDesa.push(`<option value='${desa}'>${listDesa[item][desa]}</option>`);
                        }
                        _optionDesa.push(`</optgroup>`);
                        _optionKecamatan.push(`<option>${item}</option>`);
                    }

                    $('select[name=search_kecamatan]').append(_optionKecamatan.join(''));
                    $('select[name=search_desa]').append(_optionDesa.join(''));
                }
            });

            // $.get('{{ url('api/v1/data-website') }}', {}, function(result) {
            //     let category = result.data.categoriesItems
            //     let listDesa = result.data.listDesa
            //     let listKecamatan = result.data.listKecamatan

            //     for (let index in category) {
            //         $(`.kategori-item .jumlah-${index}-elm`).text(category[index]['value'])
            //     };
            //     let _optionKecamatan = []
            //     let _optionDesa = []
            //     for (let item in listKecamatan) {
            //         _optionKecamatan.push(`<option>${item}</option>`)
            //     }

            //     for (let item in listDesa) {
            //         _optionDesa.push(`<optgroup label='${item}'>`)
            //         for (let desa in listDesa[item]) {
            //             _optionDesa.push(`<option value='${desa}'>${listDesa[item][desa]}</option>`)
            //         }
            //         _optionDesa.push(`</optgroup>`)
            //         _optionKecamatan.push(`<option>${item}</option>`)
            //     }

            //     $('select[name=search_kecamatan]').append(_optionKecamatan.join(''))
            //     $('select[name=search_desa]').append(_optionDesa.join(''))
            // }, 'json')
            const indexSearch = {
                'search': {
                    'luas_wilayah': 1,
                    'luas_pertanian': 1,
                    'luas_perkebunan': 1,
                    'luas_hutan': 1,
                    'luas_peternakan': 1
                }
            }

            const urlSummary = new URL(
                    "{{ config('app.databaseGabunganUrl') . '/api/v1/data-summary' }}");

            $.get(urlSummary, indexSearch, function(result) {
                for (let i in result.data) {
                    $(`#summary-${i}`).text(result.data[i])
                }
            }, 'json')
            // $.get('{{ url('api/v1/data-summary') }}', indexSearch, function(result) {
            //     for (let i in result.data) {
            //         $(`#summary-${i}`).text(result.data[i])
            //     }
            // }, 'json')


            var summaryPenduduk = $('#summary-penduduk').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                ordering: false,
                searchPanes: {
                    viewTotal: false,
                    columns: [0]
                },
                ajax: {
                    url: new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/wilayah/penduduk' }}"),
                    method: 'get',
                    headers: header,
                    data: function(row) {
                        return {
                            "page[size]": row.length,
                            "page[number]": (row.start / row.length) + 1,
                            "filter[search]": row.search.value,
                        };
                    },
                    dataSrc: function(json) {
                        json.recordsTotal = json.meta.pagination.total
                        json.recordsFiltered = json.meta.pagination.total

                        return json.data
                    },
                },
                columnDefs: [{
                        targets: '_all',
                        className: 'text-nowrap',
                    },
                    {
                        targets: 0,
                        render: function(data, type, row, meta) {
                            var PageInfo = $('#summary-penduduk').DataTable().page.info();
                            return PageInfo.start + meta.row + 1;
                        }
                    },
                    {
                        targets: [0, 1, 2, 3],
                        orderable: false,
                        searchable: false,
                    },
                ],
                columns: [{
                        data: null,
                    },
                    {
                        data: "attributes.nama_desa",
                        name: "nama_desa"
                    },
                    {
                        data: "attributes.nama_kecamatan",
                        name: "nama_kecamatan"
                    },
                    {
                        data: "attributes.penduduk_count",
                        name: "penduduk_count",
                        className: 'text-center'
                    },
                ],
            })

            $(function() {
                var start = moment();
                var end = moment();

                function cb(start, end) {
                    $('#reportrange span').html(start.format('D MMMM, YYYY') + ' - ' + end.format(
                        'D MMMM, YYYY'));
                }

                $('#reportrange').daterangepicker({
                    startDate: start,
                    endDate: end,
                    ranges: {
                        'Hari Ini': [moment(), moment()],
                        'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                        '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                        'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                        'Bulan Lalu': [moment().subtract(1, 'month').startOf('month'), moment()
                            .subtract(1, 'month').endOf('month')
                        ]
                    },
                    locale: {
                        format: 'D MMMM, YYYY',
                        applyLabel: 'Terapkan',
                        cancelLabel: 'Batal',
                        customRangeLabel: 'Rentang Kustom',
                        daysOfWeek: ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'],
                        monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
                            'Agustus', 'September', 'Oktober', 'November', 'Desember'
                        ],
                        firstDay: 1
                    }
                }, cb);

                cb(start, end);
            });
        });
    </script>
@endpush
