@extends('layouts.index')

@section('title', $title)

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    @include('partials.flash_message')

    {{-- Blok Filter: posisi paling atas, selebar halaman --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-filter"></i> Filter</h3></div>
                <div class="card-body">@include('dataspasial.dasbor.filter')</div>
            </div>
        </div>        
    </div>
    {{-- Card di bawah filter --}}
    <div class="row">
        <div class="col-12">
            @include('dataspasial.dasbor.kartu')
        </div>
    </div>

    {{-- Peta + Info Card --}}
    <div class="row">
        <div class="col-lg-8 col-md-12 mb-3">
            <div class="card card-outline card-primary h-100">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-map"></i> Peta</h3></div>
                <div class="card-body">
                    <div id="map" style="height: 500px; width: 100%;"></div>
                    <div id="map-empty" class="alert alert-info mt-2" style="display:none;">Tidak ada data untuk pilihan ini.</div>
                    <div id="map-unavailable" class="alert alert-warning mt-2" style="display:none;" data-testid="dataspasial-unavailable">
                        <h5 class="alert-heading">Data sementara tidak tersedia</h5>
                        <p class="mb-0">Silakan coba lagi nanti.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 mb-3">
            <div class="row h-100">
                <div class="col-12 mb-2">
                    <div class="card card-outline card-primary">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-clipboard-list"></i> Wilayah Prioritas Intervensi</h3></div>
                        <div class="card-body">
                            <div class="text-muted small">Belum ada data</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-2">
                    <div class="card card-outline card-primary">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-check-circle"></i> Verifikasi</h3></div>
                        <div class="card-body">
                            <div class="text-muted small">Belum ada data</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    

    <div class="row">
        <div class="col-lg-4 col-md-12 mb-3">
            <div class="card card-outline card-primary h-100">
                <div class="card-header"><h3 class="card-title" id="dasbor-grafik-utama-title">Grafik Indikator</h3></div>
                <div class="card-body"><div id="dasbor-grafik-kecamatan" style="min-height:240px"></div></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 mb-3">
            <div class="card card-outline card-primary h-100">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-check"></i> Kepatuhan Update & Kualitas Desa</h3></div>
                <div class="card-body">
                    <div class="text-muted small">Belum ada data</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 mb-3">
            <div class="card card-outline card-info h-100">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-question-circle"></i> Bantuan Pengisian Data</h3></div>
                <div class="card-body">
                    <div class="text-muted small">Placeholder bantuan pengisian data.</div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
@endpush

@push('js')
    @apexchartsScripts
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script src="{{ asset('js/dataspasial/peta-gabungan.js') }}"></script>
    <script src="{{ asset('js/dataspasial/dasbor.js') }}"></script>
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.DasborDataSpasial) return;
            if (window.PetaGabungan && typeof window.PetaGabungan.init === 'function') {
                try { window.PetaGabungan.init({ mapId: 'map' }); window._dasborPetaInit = true; } catch (e) { /* noop */ }
            }
            window.DasborDataSpasial.init({
                dataUrl: "{{ route('dataspasial.dasbor.data') }}",
                petaUrl: "{{ route('dataspasial.peta.data') }}",
                exportUrl: "{{ route('dataspasial.dasbor.export') }}",
                databaseGabunganUrl: "{{ config('app.databaseGabunganUrl') }}",
                identitasOpenkab: (document.querySelector('meta[name="identitas-openkab"]')||{}).getAttribute('content') || ''
            });
        });
    </script>
@endpush
