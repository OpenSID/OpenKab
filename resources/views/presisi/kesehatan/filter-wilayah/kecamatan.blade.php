<script nonce="{{ csp_nonce() }}">
    const defaultKecamatan = "{{ $defaultKecamatan == 'null' ? null : $defaultKecamatan }}";
    $('#filter_kecamatan').select2({
        placeholder: "Pilih Kecamatan"
    });
    GetListKecamatan();

    $('#filter_kecamatan').on("change", function(e) {
        GetListDesa(this.value);
    });

    function GetListKecamatan($id) {
        $('#filter_kecamatan').empty().trigger("change");
        if ($id != undefined && $id != null) {
            var optionEmpty = new Option("", "");
            $("#filter_kecamatan").append(optionEmpty);
            $.ajax({
                type: 'GET',
                url: "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-kecamatan' }}" +
                    "/" + $id,
                dataType: 'json',
                success: function(data) {
                    for (var i = 0; i < data.length; i++) {
                        if (defaultKecamatan == data[i].kode_kecamatan) {
                            var newOption = new Option(data[i].nama_kecamatan, data[i].kode_kecamatan, true,
                                true);
                        } else {
                            var newOption = new Option(data[i].nama_kecamatan, data[i].kode_kecamatan,
                                false,
                                false);
                        }
                        $("#filter_kecamatan").append(newOption);
                    }
                    $("#filter_kecamatan").trigger("change");
                },
                error: function(jqXHR, textStatus, errorThrown) {}
            });
        }
    }
</script>
