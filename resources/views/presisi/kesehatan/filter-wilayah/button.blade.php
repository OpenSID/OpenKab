$('#bt_clear_filter').click(function(){
    
    $("#filter_kabupaten").val("").trigger("change");
    $("#filter_kecamatan").val("").trigger("change");
    $("#filter_desa").val("").trigger("change");
    $('#filter_desa').empty().trigger("change");
    $('#bt_clear_filter').hide();
    window.location.href = "{{ url('presisi/kesehatan/') }}";
});
$('#bt_filter').click(function(){
    $('#bt_clear_filter').show();
    let kuartal = $('#kuartal option:selected').val() || 'null';
    let tahun = $('#tahun option:selected').val() || 'null';
    let posyandu = $('#id option:selected').val() || 'null';
    let kabupaten = $("#filter_kabupaten").val() || 'null';
    let kecamatan = $("#filter_kecamatan").val() || 'null';
    let desa = $("#filter_desa").val() || 'null';
    
    window.location.href = "{{ url('presisi/kesehatan/') }}/" + kuartal + "/" +
        tahun + "/" + posyandu + "/" + kabupaten + "/" + kecamatan + "/" + desa;
});