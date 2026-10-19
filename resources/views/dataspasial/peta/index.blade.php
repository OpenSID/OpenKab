@extends('layouts.index')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@push('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <style nonce="{{ csp_nonce() }}">
        #map {
            height: 560px;
            width: 100%;
        }

        .map-legend {
            background: #fff;
            padding: 6px 8px;
            border-radius: 4px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.4);
            line-height: 1.4;
            font-size: 12px;
        }

        .peta-popup {
            max-width: 280px;
        }

        @media (max-width: 768px) {
            #map {
                height: 380px;
            }
        }
    </style>
@endpush

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')

    <x-dataspasial-status :result="$result">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <button type="button" class="btn btn-sm btn-secondary" data-toggle="collapse"
                            data-target="#collapse-filter" data-testid="bt-toggle-filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                    <div class="card-body">
                        @include('dataspasial.peta.filter')

                        <div class="row">
                            <div class="col-md-9 mb-2">
                                <div id="map"></div>
                            </div>
                            <div class="col-md-3">
                                <div id="map-legend" class="map-legend" style="display:none;"></div>
                                <div id="map-empty" class="alert alert-info mt-2" style="display:none;">
                                    Tidak ada data untuk pilihan ini.
                                </div>
                                <div id="map-unavailable" class="alert alert-warning mt-2" style="display:none;"
                                    data-testid="dataspasial-unavailable">
                                    <h5 class="alert-heading">Data sementara tidak tersedia</h5>
                                    <p class="mb-0">Silakan coba lagi nanti.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-dataspasial-status>
@stop

@include('components.wilayah_filter_js')

@push('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script src="{{ asset('js/dataspasial/peta-gabungan.js') }}"></script>
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', function () {
            var $ = window.jQuery || window.$;
            if (!$ || !window.PetaGabungan) {
                return;
            }

            window.PetaGabungan.init({ mapId: 'map' });

            var dataUrl = "{{ route('dataspasial.peta.data') }}";

            function currentFilters() {
                return {
                    sektor_id: $('#filter_sektor').val(),
                    periode: $('#filter_periode').val(),
                    kode_kabupaten: $('#filter_kabupaten').val(),
                    kode_kecamatan: $('#filter_kecamatan').val(),
                    kode_desa: $('#filter_desa').val()
                };
            }

            function fillSelect(selector, items, placeholder) {
                var $select = $(selector);
                var current = $select.val();
                $select.empty();
                $select.append(new Option(placeholder, ''));
                items.forEach(function (item) {
                    var value = (typeof item === 'object') ? item.value : item;
                    var label = (typeof item === 'object') ? item.label : item;
                    $select.append(new Option(label, value));
                });
                if (current) {
                    $select.val(current);
                }
                if ($select.hasClass('select2') && $select.data('select2')) {
                    $select.trigger('change.select2');
                }
            }

            function applyMeta(meta) {
                meta = meta || {};

                fillSelect('#filter_periode', meta.periods || [], 'Semua Periode');
                $('#filter_periode').prop('disabled', (meta.periods || []).length === 0);

                fillSelect('#filter_tematik', meta.isian_tematik_options || [], 'Tanpa Pewarnaan');
                $('#filter_tematik').prop('disabled', (meta.isian_tematik_options || []).length === 0);
            }

            function load() {
                var params = currentFilters();

                if (!params.sektor_id) {
                    $('#map-unavailable').hide();
                    $('#map-empty').show().text('Silakan pilih sektor terlebih dahulu.');
                    return;
                }

                fetch(dataUrl + '?' + new URLSearchParams(params).toString(), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                    .then(function (response) { return response.json(); })
                    .then(function (payload) {
                        var meta = payload.meta || {};

                        if (meta.unavailable) {
                            window.PetaGabungan.unavailable(meta.message);
                            return;
                        }

                        applyMeta(meta);

                        if (meta.empty || !payload.features || payload.features.length === 0) {
                            window.PetaGabungan.empty();
                            return;
                        }

                        window.PetaGabungan.render(payload, meta);
                    })
                    .catch(function () {
                        window.PetaGabungan.unavailable();
                    });
            }

            $('#filter').on('click', function (event) {
                if (event && event.isTrigger) {
                    return;
                }
                if (!$('#filter_sektor').val()) {
                    alert('Sektor wajib dipilih terlebih dahulu.');
                    return;
                }
                load();
            });

            $(document).on('click', '#reset', function (event) {
                event.preventDefault();
                $('#filter_sektor').val('').trigger('change');
                $('#filter_periode').val('').trigger('change');
                $('#filter_tematik').val('').trigger('change');
                $('#filter_kabupaten').val('').trigger('change');
                $('#filter_kecamatan').val('').trigger('change');
                $('#filter_desa').val('').trigger('change');
                $('#map-legend').hide().empty();
                $('#map-empty').hide();
                $('#map-unavailable').hide();
            });

            $('#filter_sektor').on('change', function () {
                load();
            });
        });
    </script>
@endpush
