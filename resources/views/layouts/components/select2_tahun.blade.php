@push('js')
    <script nonce="{{ csp_nonce() }}"  >
    document.addEventListener("DOMContentLoaded", function(event) {
        $('#tahun').select2({
            minimumResultsForSearch: -1,
            theme: "bootstrap",
            ajax: {
                url: ``,
                dataType: 'json',
                beforeSend: function(jqXHR, settings) {
                    let url = '{{ $url }}';
                    let create_url = new URL(url);
                    create_url.searchParams.set('id', $('#daftar-statistik .active').data('id'));
                    settings.url =create_url;
                },
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
    });
    </script>
@endpush
