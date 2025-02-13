function GetListProgram() {
    $('#filter_program').empty().trigger("change");
    @if($id) 
        @if($id == 'penduduk')
            var optionPenduduk = new Option("Penerima Bantuan Penduduk", "penduduk");
            $("#filter_program").append(optionPenduduk);
        @elseif($id == 'keluarga')
            var optionKeluarga = new Option("Penerima Bantuan Keluarga", "keluarga");
            $("#filter_program").append(optionKeluarga);
        @else
            $.ajax({
                type: 'GET',
                url: "{{ config('app.databaseGabunganUrl').'/api/v1/statistik-web/get-list-program' }}",
                dataType: 'json',
                success: function(data) {
                    for (var i = 0; i < data.length; i++) {
                        var newOption = new Option(data[i].nama, data[i].id, true, true);
                        $("#filter_program").append(newOption);
                    }
                    $("#filter_program").val("penduduk");
                },
                error: function(jqXHR, textStatus, errorThrown) {

                }
            });
        @endif
    @else
        var optionPenduduk = new Option("Penerima Bantuan Penduduk", "penduduk");
        $("#filter_program").append(optionPenduduk);
        var optionKeluarga = new Option("Penerima Bantuan Keluarga", "keluarga");
        $("#filter_program").append(optionKeluarga);
        $.ajax({
            type: 'GET',
            url: "{{ config('app.databaseGabunganUrl').'/api/v1/statistik-web/get-list-program' }}",
            dataType: 'json',
            success: function(data) {
                for (var i = 0; i < data.length; i++) {
                    var newOption = new Option(data[i].nama, data[i].id, true, true);
                    $("#filter_program").append(newOption);
                }
                $("#filter_program").val("penduduk");
            },
            error: function(jqXHR, textStatus, errorThrown) {

            }
        });
    @endif
}