$('#filter_kecamatan').select2({
                placeholder: "Pilih Kecamatan"
    });
    $('#filter_desa').select2({
        placeholder: "Pilih Desa"
    });
    GetListKecamatan();
    GetListDesa();
    

    $('#bt_clear_filter').click(function(){
        $("#filter_kecamatan").val("").trigger("change");
        $("#filter_desa").val("").trigger("change");
        $('#filter_desa').empty().trigger("change");
        $('#bt_clear_filter').hide();
        GetListCoordinates();
        GetSummary();
    });

    $('#bt_filter').click(function(){
        $('#bt_clear_filter').show();
        GetListCoordinates( $("#filter_kecamatan").val(), $("#filter_desa").val());
        GetSummary();
    });

    $('#filter_kecamatan').on("select2:select", function(e) {
        GetListDesa(this.value);
    });
    
GetListCoordinates();

function GetListCoordinates(kecamatan = null, desa= null) {
    var coordUrl =  "{{ url('api/v1/statistik-web/get-list-coordinate') }}";
    if (kecamatan != null || desa != null){
        coordUrl =  coordUrl+= "?filter[kecamatan]=" + (kecamatan == null ? "" : kecamatan) + "&filter[desa]=" + (desa == null ? "" : desa);
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
function GetListKecamatan() {
            $('#filter_kecamatan').empty().trigger("change");
            var optionEmpty = new Option("", "");
            $("#filter_kecamatan").append(optionEmpty);
            $.ajax({
                type: 'GET',
                url: "{{ url('api/v1/statistik-web/get-list-kecamatan') }}",
                dataType: 'json',
                success: function(data) {
                    for (var i = 0; i < data.length; i++) {
                        var newOption = new Option(data[i].nama_kecamatan, data[i].kode_kecamatan, true, true);
                        $("#filter_kecamatan").append(newOption);
                    }
                    $("#filter_kecamatan").val("").trigger("change");
                },
                error: function(jqXHR, textStatus, errorThrown) {

                }
            });
        }
        function GetListDesa($id) {
            $('#filter_desa').empty().trigger("change");
            if ($id != undefined && $id != null){
                var optionEmpty = new Option("", "");
                $("#filter_desa").append(optionEmpty);
                $.ajax({
                    type: 'GET',
                    url: "{{ url('api/v1/statistik-web/get-list-desa') }}" + "/" + $id,
                    dataType: 'json',
                    success: function(data) {
                        for (var i = 0; i < data.length; i++) {
                            var newOption = new Option(data[i].nama_desa, data[i].kode_desa, true, true);
                            $("#filter_desa").append(newOption);
                        }
                        $("#filter_desa").val("");
                    },
                    error: function(jqXHR, textStatus, errorThrown) {

                    }
                });
            }
        }