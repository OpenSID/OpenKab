@push('js')
    <script nonce="{{ csp_nonce() }}"  >
    document.addEventListener("DOMContentLoaded", function(event) {
        let header = @include('layouts.components.header_bearer_api_gabungan');
        $('#status').select2({
            theme: 'bootstrap4',
            ajax: {
                url: `{{ config('app.databaseGabunganUrl').'/api/v1/penduduk/referensi/status' }}`,
                headers: header,
                dataType: 'json',
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
                                text: item.attributes.nama,
                            }
                        }),
                        pagination: {
                            more: params.page < response.meta.pagination.total_pages
                        }
                    };
                }
            }
        })

        $('#status-dasar').select2({
            theme: 'bootstrap4',
            ajax: {
                url: `{{ config('app.databaseGabunganUrl').'/api/v1/penduduk/referensi/status-dasar' }}`,
                headers: header,
                dataType: 'json',
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
                                text: item.attributes.nama,
                            }
                        }),
                        pagination: {
                            more: params.page < response.meta.pagination.total_pages
                        }
                    };
                }
            }
        })

        $('#sex').select2({
            theme: 'bootstrap4',
            ajax: {
                url: `{{ config('app.databaseGabunganUrl').'/api/v1/penduduk/referensi/sex' }}`,
                headers: header,
                dataType: 'json',
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
                                text: item.attributes.nama,
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
    </script>
@endpush
