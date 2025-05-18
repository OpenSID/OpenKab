<script nonce="{{ csp_nonce() }}">
    const defaultDesa = "{{ $defaultDesa == 'null' ? null : $defaultDesa }}";
    $('#filter_desa').select2({
        placeholder: "Pilih Desa"
    });
    GetListDesa();

    function GetListDesa($id) {
        $('#filter_desa').empty().trigger("change");
        if ($id != undefined && $id != null) {
            var optionEmpty = new Option("", "");
            $("#filter_desa").append(optionEmpty);
            $.ajax({
                type: 'GET',
                url: "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-desa' }}" + "/" +
                    $id,
                dataType: 'json',
                success: function(data) {
                    for (var i = 0; i < data.length; i++) {
                        if (defaultDesa == data[i].kode_desa) {
                            var newOption = new Option(data[i].nama_desa, data[i].kode_desa, true, true);
                        } else {
                            var newOption = new Option(data[i].nama_desa, data[i].kode_desa, false, false);
                        }
                        $("#filter_desa").append(newOption);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {}
            });
        }
    }
</script>
