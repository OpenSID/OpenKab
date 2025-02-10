@extends('layouts.presisi.index')

@section('content_header')
@stop
@section('content')
<div id="map" style="height: 500px"></div>
                
@endsection

@push('styles')
<style>
    #menu-navbar, .main-footer.ml-0{
        display: none;
    }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush
@push('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script nonce="{{ csp_nonce() }}" type="text/javascript">
document.addEventListener("DOMContentLoaded", function (event) {
    "use strict";
    const position = [{{ env('LATTITUDE_MAP', -8.459556) }}, {{ env('LONGITUDE_MAP', 115.046600) }}]
    const map = L.map('map').setView( position, 13);
    const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	}).addTo(map)

    @include('peta.lokasi.peta.index')
    @include('peta.lokasi.peta.style')
    



    
});


</script>
@endpush
