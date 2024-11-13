$('#filter_kabupaten').select2({
    placeholder: "Pilih Kabupaten"
});
GetListKabupaten();
$('#filter_kabupaten').on("select2:select", function(e) {
    GetListKecamatan(this.value);
});
function GetListKabupaten() {
    $('#filter_kabupaten').empty().trigger("change");
    var optionEmpty = new Option("", "");
    $("#filter_kabupaten").append(optionEmpty);
    $.ajax({
        type: 'GET',
        url: "{{ url('api/v1/statistik-web/get-list-kabupaten') }}",
        dataType: 'json',
        success: function(data) {
            for (var i = 0; i < data.length; i++) {
                var newOption = new Option(data[i].nama_kabupaten, data[i].kode_kabupaten, true, true);
                $("#filter_kabupaten").append(newOption);
            }
            $("#filter_kabupaten").val("").trigger("change");
        },
        error: function(jqXHR, textStatus, errorThrown) {
        }
    });
}