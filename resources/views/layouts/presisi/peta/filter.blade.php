$('#filter_kabupaten').select2({
    placeholder: "Pilih Kabupaten"
});
$('#filter_kecamatan').select2({
    placeholder: "Pilih Kecamatan"
});
$('#filter_desa').select2({
    placeholder: "Pilih Desa"
});

GetListKabupaten();
GetListKecamatan();
GetListDesa();
    

    $('#bt_clear_filter').click(function(){
        $("#filter_kabupaten").val("").trigger("change");
        $("#filter_kecamatan").val("").trigger("change");
        $("#filter_desa").val("").trigger("change");
        $('#filter_desa').empty().trigger("change");
        $('#bt_clear_filter').hide();
        GetListCoordinates();
        GetSummary();
    });

    $('#bt_filter').click(function(){
        $('#bt_clear_filter').show();
        GetListCoordinates( $("#filter_kabupaten").val(), $("#filter_kecamatan").val(), $("#filter_desa").val());
        GetSummary();
    });

    $('#filter_kabupaten').on("select2:select", function(e) {
        GetListKecamatan(this.value);
    });

    $('#filter_kecamatan').on("select2:select", function(e) {
        GetListDesa(this.value);
    });
    