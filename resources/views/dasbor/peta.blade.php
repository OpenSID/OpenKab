<div class="col-12">
    <div class="card">
        <div class="card-body">
            <div id="map"></div>
        </div>
    </div>
</div>

@push('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            var markerIcon = new L.Icon({
                iconUrl: '{{ asset('assets/img/map/marker-icon-2x-red.png') }}',
                shadowUrl: '{{ asset('assets/img/map/marker-shadow.png') }}',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });
            const position = [{{ env('LATTITUDE_MAP', -8.459556) }}, {{ env('LONGITUDE_MAP', 115.0466) }}]
            const map = L.map('map').setView(position, 13);
            const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map)

            $('#map').change(function() {
                const header = @include('layouts.components.header_bearer_api_gabungan');
                const kabupaten = $("#filter_kabupaten").val()
                const kecamatan = $("#filter_kecamatan").val()
                const desa = $("#filter_desa").val()
                var coordUrl =
                    "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-coordinate' }}";
                if (kecamatan != null || desa != null) {
                    coordUrl = coordUrl += "?filter[kabupaten]=" + (kabupaten == null ? "" : kabupaten) +
                        "&filter[kecamatan]=" + (kecamatan == null ? "" : kecamatan) + "&filter[desa]=" + (
                            desa == null ? "" : desa);
                }
                $.ajax({
                    type: 'GET',
                    url: coordUrl,
                    headers: header,
                    dataType: 'json',
                    success: function(data) {
                        map.eachLayer((layer) => {
                            if (layer instanceof L.Marker) {
                                layer.remove();
                            }
                        });
                        var isFirst = true;
                        for (var i = 0; i < data.length; i++) {
                            if (data[i].lat != null && data[i].lng != null) {
                                var marker = L.marker([parseFloat(data[i].lat), parseFloat(data[
                                        i].lng)], {
                                        icon: markerIcon
                                    }).bindPopup("<b style='font-size:12pt;'>Provinsi :" + data[
                                            i].nama_propinsi +
                                        "</b><br><b style='font-size:12pt;'>Kota :" + data[i]
                                        .nama_kabupaten +
                                        "</b><br><b style='font-size:12pt;'>Kecamatan :" + data[
                                            i].nama_kecamatan +
                                        "</b><br><b style='font-size:12pt;'>Desa :" + data[i]
                                        .nama_desa + "</b>")
                                    .addTo(map)
                                marker.on('mouseover', function(ev) {
                                    ev.target.openPopup();
                                });
                                if (isFirst) {
                                    isFirst = false;
                                    map.panTo(new L.LatLng(parseFloat(data[i].lat), parseFloat(
                                        data[i].lng)));
                                }
                            }
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {}
                });
            })

            $('#map').trigger('change');
        });
    </script>
@endpush

@push('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style nonce="{{ csp_nonce() }}">
        #map {
            height: 250px;
        }
    </style>
@endpush
