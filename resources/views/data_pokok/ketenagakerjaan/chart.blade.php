@include('data_pokok.data_presisi.partials.chart')
<script nonce="{{ csp_nonce() }}">
    function grafikPie(filters) {
        loadChartData(filters, 'barChart', 'bar', 'jumlah_penghasilan');
        loadChartData(filters, 'donutChart', 'doughnut', 'pelatihan');
    }

    function loadChartData(filters, canvasId, chartType, kategori) {
        var params = {
            kategori: kategori,
            'kode_kabupaten': filters ? filters.kodeKabupaten : '',
            'kode_kecamatan': filters ? filters.kodeKecamatan : '',
            'config_desa': filters ? filters.configDesa : '',
        };
        apiProxyGet('ketenagakerjaan/statistik', params, function(json) {
            var data = [];
            if (json.data && json.data.length > 0) {
                json.data.forEach(function(item) {
                    var attrs = item.attributes;
                    if (attrs.nilai && ['JUMLAH', 'TOTAL'].includes(attrs.nilai.toUpperCase())) return;
                    data.push(attrs);
                });
            }
            tampilChart(chartType, canvasId, data);
        });
    }
</script>
