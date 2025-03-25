@push('js')
    <script nonce="{{ csp_nonce() }}"  >
    document.addEventListener("DOMContentLoaded", function(event) {

        const header = @include('layouts.components.header_bearer_api_gabungan');

        var urlDusun = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/wilayah/dusun' }}");
        var urlRw = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/wilayah/rw' }}");
        var urlRt = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/wilayah/rt' }}");

        $('#dusun').select2({
            theme: 'bootstrap4',
            ajax: {
                url: urlDusun,
                dataType: 'json',
                headers: header,
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
                        results: $.map(response.data, function(item) {
                            return {
                                id: item.id,
                                text: item.attributes.dusun,
                            }
                        }),
                        pagination: {
                            more: params.page < response.meta.pagination.total_pages
                        }
                    };
                }
            }
        })

        $('#dusun').on('select2:select', function (e) {
            $('#rw').attr('disabled', false);
            $('#rw').val('').trigger('change');

            $('#rw').select2({
                theme: 'bootstrap4',
                ajax: {
                    url: urlRw,
                    headers: header,
                    dataType: 'json',
                    delay: 400,
                    data: function(params) {
                        return {
                            "filter[subrw]": e.params.data.id,
                            "filter[search]": params.term,
                            "page[number]": params.page
                        };
                    },
                    processResults: function(response, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(response.data, function(item) {
                                return {
                                    id: item.attributes.rw,
                                    text: item.attributes.rw,
                                }
                            }),
                            pagination: {
                                more: params.page < response.meta.pagination.total_pages
                            }
                        };
                    }
                }
            })
        })

        $('#rw').on('select2:select', function (e) {
            $('#rt').attr('disabled', false);
            $('#rt').val('').trigger('change');
            $('#rt').select2({
                theme: 'bootstrap4',
                ajax: {
                    url: urlRt,
                    headers: header,
                    dataType: 'json',
                    delay: 400,
                    data: function(params) {
                        return {
                            "filter[rw]": e.params.data.id,
                            "filter[subdusun]": $('#dusun').val(),
                            "filter[search]": params.term,
                            "page[number]": params.page
                        };
                    },
                    processResults: function(response, params) {
                        params.page = params.page || 1;

                        return {
                            results: $.map(response.data, function(item) {
                                return {
                                    id: item.attributes.rt,
                                    text: item.attributes.rt,
                                }
                            }),
                            pagination: {
                                more: params.page < response.meta.pagination.total_pages
                            }
                        };
                    }
                }
            })
        })
    })
    </script>
@endpush
