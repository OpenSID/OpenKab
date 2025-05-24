@push('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {

            const headerFilter = @include('layouts.components.header_bearer_api_gabungan');
            let urlKabupaten =
                "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-kabupaten' }}";
            let urlKecamatan =
                "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-kecamatan' }}";
            let urlDesa =
                "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-desa' }}";

            $('#filter_kabupaten').select2({
                theme: 'bootstrap4',
                ajax: {
                    url: urlKabupaten,
                    dataType: 'json',
                    headers: headerFilter,
                    delay: 400,
                    data: function(params) {
                        return {
                            "filter[search]": params.term,
                            "page[number]": params.page
                        };
                    },
                    processResults: function(response, params) {
                        params.page = params.page || 1;
                        return {
                            results: $.map(response, function(item) {
                                return {
                                    id: item.kode_kabupaten,
                                    text: item.nama_kabupaten,
                                }
                            }),
                        };
                    },
                },
                placeholder: "Pilih Kabupaten",
                cache: true,
                allowClear: true,
                width: '100%',
            })

            $('#filter_kecamatan').select2({
                placeholder: "Pilih Kecamatan",
                allowClear: true,
                width: '100%',
            })

            $('#filter_desa').select2({
                placeholder: "Pilih Desa",
                allowClear: true,
                width: '100%',
            })

            $('#filter_kabupaten').on('change', function() {
                let id = $(this).val();
                $('#filter_kecamatan').empty().trigger("change");
                if (id != undefined && id != null) {
                    $.ajax({
                        url: urlKecamatan + '/' + id,
                        type: 'GET',
                        headers: headerFilter,
                        dataType: 'json',
                        data: {
                            "filter[search]": id
                        },
                    }).done(function(data) {
                        $('#filter_kecamatan').empty().trigger("change");
                        var optionEmpty = new Option("", "");
                        $("#filter_kecamatan").append(optionEmpty);
                        for (var i = 0; i < data.length; i++) {
                            var newOption = new Option(data[i].nama_kecamatan, data[i]
                                .kode_kecamatan, false, false);
                            $("#filter_kecamatan").append(newOption);
                        }
                    })
                }
            })

            $('#filter_kecamatan').on('change', function() {
                let id = $(this).val();
                $('#filter_desa').empty().trigger("change");
                if (id != undefined && id != null) {
                    $.ajax({
                        url: urlDesa + '/' + id,
                        type: 'GET',
                        headers: headerFilter,
                        dataType: 'json',
                        data: {
                            "filter[search]": id
                        },
                    }).done(function(data) {
                        $('#filter_desa').empty().trigger("change");
                        var optionEmpty = new Option("", "");
                        $("#filter_desa").append(optionEmpty);
                        for (var i = 0; i < data.length; i++) {
                            var newOption = new Option(data[i].nama_desa, data[i]
                                .kode_desa, false, false);
                            $("#filter_desa").append(newOption);
                        }
                    })
                }
            })
        })
    </script>
@endpush
