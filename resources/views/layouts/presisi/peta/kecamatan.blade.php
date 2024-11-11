function GetListKecamatan($id) {
            $('#filter_kecamatan').empty().trigger("change");
            if ($id != undefined && $id != null){
                var optionEmpty = new Option("", "");
                $("#filter_kecamatan").append(optionEmpty);
                $.ajax({
                    type: 'GET',
                    url: "{{ url('api/v1/statistik-web/get-list-kecamatan') }}" + "/" + $id,
                    dataType: 'json',
                    success: function(data) {
                        for (var i = 0; i < data.length; i++) {
                            var newOption = new Option(data[i].nama_kecamatan, data[i].kode_kecamatan, true, true);
                            $("#filter_kecamatan").append(newOption);
                        }
                        $("#filter_kecamatan").val("");
                    },
                    error: function(jqXHR, textStatus, errorThrown) {

                    }
                });
            }
        }