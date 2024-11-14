$('#bt_clear_filter').click(function(){
    
    $("#filter_kabupaten").val("").trigger("change");
    $("#filter_kecamatan").val("").trigger("change");
    $("#filter_desa").val("").trigger("change");
    $('#filter_desa').empty().trigger("change");
    $('#bt_clear_filter').hide();
    table.ajax.reload();
});
$('#bt_filter').click(function(){
    $('#bt_clear_filter').show();
    table.ajax.reload();
});