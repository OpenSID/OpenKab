@push('js')
    <script>
        $('#tahun').select2({
            minimumResultsForSearch: -1,
            theme: "bootstrap",
            ajax: {
                url: `{{ $url }}`,
                dataType: 'json',
                processResults: function(data) {
                    if (data.success != true) {
                        return null
                    };
                    const element = new Array();

                    for (let index = data.data.tahun_akhir; index >= data.data
                        .tahun_awal; index--) {
                        element.push({
                            id: index,
                            text: index
                        });
                    }

                    return {
                        results: element
                    };
                }
            },
            placeholder: "Pilih Tahun",
        });
    </script>
@endpush
