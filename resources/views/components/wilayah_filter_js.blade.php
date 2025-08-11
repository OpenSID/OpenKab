@push('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            let urlKabupaten =
                "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-kabupaten' }}";
            let urlKecamatan =
                "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-kecamatan' }}";
            let urlDesa =
                "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-desa' }}";

            $.get(urlKabupaten, {}, function(data) {
                var optionEmpty = new Option("", "");
                $("#filter_kabupaten").append(optionEmpty);

                for (var i = 0; i < data.length; i++) {
                    var newOption = new Option(data[i].nama_kabupaten, data[i]
                        .kode_kabupaten, false, false);
                    $("#filter_kabupaten").append(newOption);
                }
            }, 'json')

            $('#filter_kabupaten').select2({
                placeholder: "{{ config('app.sebutanKab') }}",
                allowClear: true,
                height: '100%',
                width: '100%',
            })

            $('#filter_kecamatan').select2({
                placeholder: "Pilih Kecamatan",
                allowClear: true,
                width: '100%',
            })

            $('#filter_desa').select2({
                placeholder: "{{ config('app.sebutanDesa') }}",
                allowClear: true,
                width: '100%',
            })

            $('#filter_kabupaten').on('change', function() {
                let id = $(this).val();
                $('#filter_kecamatan').empty();
                $('#filter_kecamatan').trigger("change");
                $('#filter_kecamatan').prop('disabled', true);
                if (id) {
                    $.ajax({
                        url: urlKecamatan + '/' + id,
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            "filter[search]": id
                        },
                    }).done(function(data) {
                        var optionEmpty = new Option("", "");
                        $("#filter_kecamatan").append(optionEmpty);
                        for (var i = 0; i < data.length; i++) {
                            var newOption = new Option(data[i].nama_kecamatan, data[i]
                                .kode_kecamatan, false, false);
                            $("#filter_kecamatan").append(newOption);
                        }
                        $('#filter_kecamatan').prop('disabled', false);
                    })
                }
            })

            $('#filter_kecamatan').on('change', function() {
                let id = $(this).val();
                $('#filter_desa').empty();
                $('#filter_desa').prop('disabled', true);
                if (id) {
                    $.ajax({
                        url: urlDesa + '/' + id,
                        type: 'GET',
                        dataType: 'json',
                        data: {
                            "filter[search]": id
                        },
                    }).done(function(data) {
                        var optionEmpty = new Option("", "");
                        $("#filter_desa").append(optionEmpty);
                        for (var i = 0; i < data.length; i++) {
                            var newOption = new Option(data[i].nama_desa, data[i]
                                .kode_desa, false, false);
                            $("#filter_desa").append(newOption);
                        }
                        $('#filter_desa').prop('disabled', false);
                    })
                }
            })
        })
    </script>
@endpush
