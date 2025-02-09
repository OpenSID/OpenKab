function GetListCoordinates(kabupaten = null, kecamatan = null, desa= null) {
    var coordUrl =  "{{ url('api/v1/plan/get-list-coordinate/'.$parent.'/'.$id) }}";
    
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
                    var marker = L.marker([parseFloat(data[i].lat), parseFloat(data[i].lng)], {icon: markerIcon}).bindPopup("<b style='font-size:12pt;'>Lokasi :" + data[i].nama)
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