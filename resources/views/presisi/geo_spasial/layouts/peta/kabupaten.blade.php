function GetListKabupaten() {
    const header = @include('layouts.components.header_bearer_api_gabungan');

    $('#filter_kabupaten').empty().trigger("change");

    // Ambil kode_kabupaten dari session Laravel
    var sessionKodeKabupaten = "{{ session('kabupaten.kode_kabupaten') }}";

    var url = "{{ config('app.databaseGabunganUrl').'/api/v1/statistik-web/get-list-kabupaten' }}";

    // Tambahkan query filter jika session tersedia
    if (sessionKodeKabupaten) {
        url += '?filter[kode_kabupaten]=' + sessionKodeKabupaten;
    }

    var optionEmpty = new Option("", "");
    $("#filter_kabupaten").append(optionEmpty);

    $.ajax({
        type: 'GET',
        url: url,
        dataType: 'json',
        success: function(data) {
            for (var i = 0; i < data.length; i++) {
                var isSelected = data[i].kode_kabupaten === sessionKodeKabupaten;
                var newOption = new Option(data[i].nama_kabupaten, data[i].kode_kabupaten, isSelected, isSelected);
                $("#filter_kabupaten").append(newOption);
            }

            if (sessionKodeKabupaten) {
                $("#filter_kabupaten").val(sessionKodeKabupaten);
                GetListKecamatan(sessionKodeKabupaten); // Panggil langsung
                GetListCoordinates(sessionKodeKabupaten); // Panggil langsung
                $("#filter_kabupaten").trigger("change");
            } else {
                $("#filter_kabupaten").val("").trigger("change");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Gagal mengambil data kabupaten:", textStatus);
        }
    });
}
