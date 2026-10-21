@push('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <style nonce="{{ csp_nonce() }}">
        #map { height: 560px; width: 100%; }
        @media (max-width: 767.98px) { #map { height: 380px; } }
    </style>
@endpush

<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title"><i class="fas fa-map"></i> Peta</h3></div>
    <div class="card-body">
        <div id="map"></div>
        <div id="map-empty" class="alert alert-info mt-2" style="display:none;">Tidak ada data untuk pilihan ini.</div>
        <div id="map-unavailable" class="alert alert-warning mt-2" style="display:none;" data-testid="dataspasial-unavailable">
            <h5 class="alert-heading">Data sementara tidak tersedia</h5>
            <p class="mb-0">Silakan coba lagi nanti.</p>
        </div>
    </div>
</div>

@push('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script src="{{ asset('js/dataspasial/peta-gabungan.js') }}"></script>
@endpush
