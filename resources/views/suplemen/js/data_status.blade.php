var url = new URL("{{ config('app.databaseGabunganUrl').'/api/v1/suplemen' }}/status");
$('#status').select2({
    theme: 'bootstrap4',
    minimumResultsForSearch: -1,
    ajax: {
        url: url,
        headers: header,
        dataType: 'json',
        processResults: function(response) {
            return {
                results: response.data.map(function(item) {
                    return {
                        id: item.id,
                        text: item.nama
                    }
                })
            };
        }
    },
});