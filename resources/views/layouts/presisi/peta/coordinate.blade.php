function GetListCoordinates(kabupaten = null, kecamatan = null, desa= null) {
    var coordUrl =  "{{ url('api/v1/statistik-web/get-list-coordinate') }}";
    if (kecamatan != null || desa != null){
        coordUrl =  coordUrl+= "?filter[kabupaten]=" + (kabupaten == null ? "" : kabupaten) + "&filter[kecamatan]=" + (kecamatan == null ? "" : kecamatan) + "&filter[desa]=" + (desa == null ? "" : desa);
    }
    $.ajax({
        type: 'GET',
        url: coordUrl,
        dataType: 'json',
        success: function(data) {
            map.eachLayer((layer) => {
            if (layer instanceof L.Marker) {
                layer.remove();
            }
            });
            var isFirst = true;
            for (var i=0; i < data.length; i++ ){
                if (data[i].lat !=null && data[i].lng != null){
                    var marker = L.marker([parseFloat(data[i].lat), parseFloat(data[i].lng)], {icon: markerIcon}).bindPopup("<b style='font-size:12pt;'>Provinsi :" + data[i].nama_propinsi + "</b><br><b style='font-size:12pt;'>Kota :" + data[i].nama_kabupaten + "</b><br><b style='font-size:12pt;'>Kecamatan :" + data[i].nama_kecamatan + "</b><br><b style='font-size:12pt;'>Desa :" + data[i].nama_desa + "</b>")
                    .addTo(map)
                    marker.on('mouseover',function(ev) {
                        ev.target.openPopup();
                    });
                    if (isFirst){
                        isFirst = false;
                        map.panTo(new L.LatLng(parseFloat(data[i].lat), parseFloat(data[i].lng)));
                    }
                }
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
        }
    });
}