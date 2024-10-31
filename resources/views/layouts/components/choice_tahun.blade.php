@push('js')
<script nonce="{{ csp_nonce() }}">
    document.addEventListener("DOMContentLoaded", function(event) {
            const tahunSelect = document.getElementById('tahun');
            const choices = new Choices(tahunSelect, {
                placeholderValue: 'Pilih Tahun',
                searchEnabled: true,
                shouldSort: false,
            });

            function fetchTahunOptions() {
                const url = '{{ $url }}';
                const createUrl = new URL(url);
                createUrl.searchParams.set('id', '{{ $default_id }}');

                fetch(createUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) return;

                        const tahunOptions = [];
                        for (let index = data.data.tahun_akhir; index >= data.data.tahun_awal; index--) {
                            tahunOptions.push({
                                value: index,
                                label: index
                            });
                        }

                        choices.setChoices(tahunOptions, 'value', 'label', true);
                    })
                    .catch(error => console.error('Error fetching data:', error));
            }

            fetchTahunOptions();
        });
</script>
@endpush