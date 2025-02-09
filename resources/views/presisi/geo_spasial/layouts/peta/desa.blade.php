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