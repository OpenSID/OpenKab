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

        #map-layers .layer-group {
            font-size: 0.875rem;
        }

        #map-layers .form-check {
            margin-bottom: 0.15rem;
        }

        .map-legend {
            background: #fff;
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            border-radius: 4px;
            line-height: 1.4;
            font-size: 0.8rem;
        }

        .peta-popup {
            max-width: 280px;
        }

        @media (max-width: 767.98px) {
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
            {{-- Blok 1: Filter DataSpasial --}}
            <div class="col-md-3">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-filter"></i> Filter</h3>
                    </div>
                    <div class="card-body">
                        @include('dataspasial.peta.filter')
                    </div>
                </div>
            </div>

            {{-- Blok 2: Peta + panel lapisan --}}
            <div class="col-md-9">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-map"></i> Peta</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9 mb-2">
                                <div id="map"></div>
                                <div id="map-empty" class="alert alert-info mt-2" style="display:none;">
                                    Tidak ada data untuk pilihan ini.
                                </div>
                                <div id="map-unavailable" class="alert alert-warning mt-2" style="display:none;"
                                    data-testid="dataspasial-unavailable">
                                    <h5 class="alert-heading">Data sementara tidak tersedia</h5>
                                    <p class="mb-0">Silakan coba lagi nanti.</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <h5 class="mb-2">Lapisan</h5>
                                <div id="map-layers" data-testid="map-layers">
                                    <p class="text-muted small mb-0">Belum ada lapisan.</p>
                                </div>
                                <h5 class="mt-3 mb-2">Legenda</h5>
                                <div id="map-legend" class="map-legend" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-dataspasial-status>
@stop

@push('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script src="{{ asset('js/dataspasial/peta-gabungan.js') }}"></script>
    <script nonce="{{ csp_nonce() }}">
        window.PETA_GABUNGAN_ICON = {
            iconUrl: "{{ asset('assets/img/map/marker-icon-2x-red.png') }}",
            shadowUrl: "{{ asset('assets/img/map/marker-shadow.png') }}"
        };
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.PetaGabungan || !window.L) {
                return;
            }

            window.PetaGabungan.init({ mapId: 'map' });

            var databaseGabunganUrl = "{{ config('app.databaseGabunganUrl') }}";
            var identityMeta = document.querySelector('meta[name="identitas-openkab"]');
            var identitasOpenkab = identityMeta ? identityMeta.getAttribute('content') : '';
            var dataUrl = "{{ route('dataspasial.peta.data') }}";

            var state = {
                sektor: null,
                kecamatan: '',
                filters: {},
                lastRequestId: 0
            };

            function currentKabupaten() {
                return identitasOpenkab;
            }

            function loadKecamatan() {
                if (!currentKabupaten()) {
                    return;
                }

                fetch(databaseGabunganUrl + '/api/v1/statistik-web/get-list-kecamatan/' + currentKabupaten(), {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        var select = document.getElementById('filter_kecamatan');
                        if (!select) {
                            return;
                        }

                        select.innerHTML = '<option value="">Semua Kecamatan</option>';
                        (data || []).forEach(function (item) {
                            var option = document.createElement('option');
                            option.value = item.kode_kecamatan;
                            option.textContent = item.nama_kecamatan;
                            select.appendChild(option);
                        });
                    })
                    .catch(function () {
                        /* diamkan; filter kecamatan opsional */
                    });
            }

            function renderDynamicFilters(filters) {
                var container = document.getElementById('peta-filters');
                if (!container) {
                    return;
                }

                if (!filters || filters.length === 0) {
                    container.innerHTML = '<p class="text-muted small mb-0">Tidak ada filter isian untuk lapisan ini.</p>';
                    return;
                }

                var html = '';
                filters.forEach(function (filter) {
                    var kode = filter.kode;
                    var id = 'peta-filter-' + kode;
                    html += '<div class="form-group"><label for="' + id + '">' + (filter.label || kode) + '</label>';

                    if (filter.tipe === 'pilihan' && Array.isArray(filter.opsi) && filter.opsi.length > 0) {
                        html += '<select id="' + id + '" class="form-control form-control-sm" data-filter="' + kode + '">' +
                            '<option value="">Semua</option>' +
                            filter.opsi.map(function (opsi) {
                                return '<option value="' + opsi + '">' + opsi + '</option>';
                            }).join('') +
                            '</select>';
                    } else if (filter.tipe === 'angka') {
                        html += '<input type="number" step="any" id="' + id + '" class="form-control form-control-sm" data-filter="' + kode + '">';
                    } else {
                        html += '<input type="text" id="' + id + '" class="form-control form-control-sm" data-filter="' + kode + '">';
                    }

                    html += '</div>';
                });

                container.innerHTML = html;
            }

            function renderThematicOptions(options) {
                var select = document.getElementById('filter_tematik');
                if (!select) {
                    return;
                }

                select.innerHTML = '<option value="">Tanpa Pewarnaan</option>';
                (options || []).forEach(function (option) {
                    var el = document.createElement('option');
                    el.value = option.value;
                    el.textContent = option.label || option.value;
                    select.appendChild(el);
                });

                select.disabled = !(options && options.length > 0);
                select.value = '';
                window.PetaGabungan.setThematic(null);
            }

            function collectSelectedFilters() {
                var result = {};
                document.querySelectorAll('#peta-filters [data-filter]').forEach(function (input) {
                    if (input.value !== '') {
                        result[input.getAttribute('data-filter')] = input.value;
                    }
                });
                return result;
            }

            function activeSektor() {
                return state.sektor;
            }

            function load(options) {
                options = options || {};
                var requestId = ++state.lastRequestId;

                var params = new URLSearchParams();
                if (activeSektor()) {
                    params.set('sektor', activeSektor());
                }
                if (state.kecamatan) {
                    params.set('kode_kecamatan', state.kecamatan);
                }
                var filters = options.keepFilters ? state.filters : collectSelectedFilters();
                state.filters = filters;
                Object.keys(filters).forEach(function (kode) {
                    params.set('filter[' + kode + ']', filters[kode]);
                });

                fetch(dataUrl + '?' + params.toString(), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                    .then(function (response) { return response.json(); })
                    .then(function (payload) {
                        if (requestId !== state.lastRequestId) {
                            return;
                        }

                        var meta = payload.meta || {};
                        if (meta.unavailable) {
                            window.PetaGabungan.unavailable(meta.message);
                            return;
                        }

                        renderDynamicFilters(meta.filters || []);
                        renderThematicOptions(meta.isian_tematik_options || []);
                        window.PetaGabungan.render(payload, meta);

                        if (meta.empty) {
                            window.PetaGabungan.empty();
                        }
                    })
                    .catch(function () {
                        if (requestId === state.lastRequestId) {
                            window.PetaGabungan.unavailable();
                        }
                    });
            }

            // Lapisan DataSpasial menentukan konteks sektor.
            var layersContainer = document.getElementById('map-layers');
            if (layersContainer) {
                layersContainer.addEventListener('change', function (event) {
                    var input = event.target;
                    if (!input || input.type !== 'checkbox' || !input.getAttribute('data-layer')) {
                        return;
                    }

                    var key = input.getAttribute('data-layer');
                    if (key.indexOf('data:') === 0) {
                        state.sektor = input.checked ? key.substring('data:'.length) : null;
                        load();
                    }
                });
            }

            var kecamatanSelect = document.getElementById('filter_kecamatan');
            if (kecamatanSelect) {
                kecamatanSelect.addEventListener('change', function () {
                    state.kecamatan = kecamatanSelect.value;
                });
            }

            var thematicSelect = document.getElementById('filter_tematik');
            if (thematicSelect) {
                thematicSelect.addEventListener('change', function () {
                    var option = (state.thematicOptions || []).filter(function (item) {
                        return item.value === thematicSelect.value;
                    })[0];
                    window.PetaGabungan.setThematic(option || null);
                });
            }

            var applyButton = document.getElementById('peta-filter');
            if (applyButton) {
                applyButton.addEventListener('click', function () {
                    load();
                });
            }

            var resetButton = document.getElementById('peta-reset');
            if (resetButton) {
                resetButton.addEventListener('click', function () {
                    state.sektor = null;
                    state.kecamatan = '';
                    state.filters = {};
                    if (kecamatanSelect) {
                        kecamatanSelect.value = '';
                    }
                    document.querySelectorAll('#map-layers input[type=checkbox]').forEach(function (input) {
                        input.checked = true;
                    });
                    load();
                });
            }

            // Simpan opsi tematik untuk lookup saat change.
            var originalRender = window.PetaGabungan.render;
            window.PetaGabungan.render = function (geojson, meta) {
                state.thematicOptions = (meta && meta.isian_tematik_options) || [];
                return originalRender(geojson, meta);
            };

            loadKecamatan();
            load();
        });
    </script>
@endpush
