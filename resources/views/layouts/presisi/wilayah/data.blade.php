function GetSummary(kabupaten = null, kecamatan = null, desa = null) {
    const indexSearch = {
        search: {
            luas_wilayah: 1,
            luas_pertanian: 1,
            luas_perkebunan: 1,
            luas_hutan: 1,
            luas_peternakan: 1
        },
        filter: {
            kode_kabupaten: kabupaten || '',
            kode_kecamatan: kecamatan || '',
            kode_desa: desa || ''
        }
    };

    const urlSummary = new URL(
                    "{{ config('app.databaseGabunganUrl') . '/api/v1/data-summary' }}");

    $.get(urlSummary, indexSearch, function(result) {
        for (let i in result.data) {
            $(`#summary-${i}`).text(result.data[i]);
        }
    }, 'json');
    {{-- $.get("{{ url('api/v1/data-summary') }}", indexSearch, function(result) {
        for (let i in result.data) {
            $(`#summary-${i}`).text(result.data[i]);
        }
    }, 'json'); --}}
}
