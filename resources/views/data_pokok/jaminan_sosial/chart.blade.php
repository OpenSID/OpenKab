@include('data_pokok.data_presisi.partials.chart')
<script nonce="{{ csp_nonce() }}">
    function grafikPie(filters) {
        $('#pie1').html('<canvas id="donutChart1"></canvas>');
        $('#pie2').html('<canvas id="donutChart2"></canvas>');
        $('#pie4').html('<canvas id="donutChart3"></canvas>');
        loadChartData(filters, 'donutChart1', 'doughnut', 'jns_bantuan');
        loadChartData(filters, 'donutChart2', 'doughnut', 'jns_gangguan_mental');
        loadChartData(filters, 'donutChart3', 'doughnut', 'terapi_gangguan_mental');
    }

    function loadChartData(filters, canvasId, chartType, kategori) {
        var params = {
            kategori: kategori,
            'tahun': $('#filter-tahun').val(),
            'filter[status_kelengkapan]': $('#filter-status-kelengkapan').val(),
            'kode_kabupaten': filters ? filters.kodeKabupaten : '',
            'kode_kecamatan': filters ? filters.kodeKecamatan : '',
            'config_desa': filters ? filters.configDesa : '',
        };
        apiProxyGet('data-presisi/jaminan-sosial/statistik', params, function(json) {
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
