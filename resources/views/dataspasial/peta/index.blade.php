@extends('layouts.index')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <style>
        #map { height: 480px; width: 100%; }
        .peta-popup { max-width: 280px; }
        .map-legend { position: absolute; bottom: 10px; left: 10px; background: #fff; padding: 6px 8px; border-radius: 4px; box-shadow: 0 1px 5px rgba(0,0,0,0.4); z-index: 1000; }
        @media (max-width: 768px) { #map { height: 360px; } }
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
                        <h3 class="card-title">Peta Gabungan</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-group mb-2">
                                <label for="filter-sektor">Sektor <span class="text-danger">*</span></label>
                                <select id="filter-sektor" class="form-control form-control-sm" required>
                                    <option value="">Pilih Sektor</option>
                                    @if(isset($sektor) && $sektor->isData())
                                        @foreach($sektor->data as $item)
                                            @php $attr = $item['attributes'] ?? []; $id = $item['id'] ?? ''; @endphp
                                            <option value="{{ $id }}">{{ $attr['nama'] ?? $attr['kode'] ?? $id }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        @include('components.wilayah_filter')
                        <div class="position-relative">
                            <div id="map"></div>
                            <div id="map-legend" class="map-legend" style="display:none;"></div>
                        </div>
                        <div id="map-empty" class="alert alert-info mt-2" style="display:none;">Tidak ada data untuk pilihan ini.</div>
                        <div id="map-unavailable" class="alert alert-warning mt-2" style="display:none;" data-testid="dataspasial-unavailable">
                            <h5 class="alert-heading">Data sementara tidak tersedia</h5>
                            <p class="mb-0">Silakan coba lagi nanti.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-dataspasial-status>
@stop

@push('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script src="{{ asset('js/dataspasial/peta-gabungan.js') }}"></script>
    <script nonce="{{ csp_nonce() }}">
        $(function () {
            if (window.PetaGabungan) {
                window.PetaGabungan.init({mapId: 'map'});
            }
        });
    </script>
@endpush
