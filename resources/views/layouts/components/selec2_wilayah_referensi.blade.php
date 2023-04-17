@push('js')
    <script>
        $('#dusun').select2({
            ajax: {
                url: `{{ url('api/v1/wilayah/dusun') }}`,
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
                ajax: {
                    url: `{{ url('api/v1/wilayah/rw') }}`,
                    dataType: 'json',
                    delay: 400,
                    data: function(params) {
                        return {
                            "filter[subrw]": e.params.data,
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
                ajax: {
                    url: `{{ url('api/v1/wilayah/rt') }}`,
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
                                    id: item.id,
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
    </script>
@endpush