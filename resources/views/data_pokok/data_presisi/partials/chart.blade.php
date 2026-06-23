<script nonce="{{ csp_nonce() }}">
    var _chartInstances = {};
    function tampilChart(type, canvasId, rawData, chartOptions = {}) {
        var labels = [];
        var counts = [];
        var backgroundColors = [];

        if (rawData && rawData.labels && rawData.datasets) {
            labels = rawData.labels;
            counts = rawData.datasets[0] ? rawData.datasets[0].data : [];
            backgroundColors = rawData.datasets[0] ? rawData.datasets[0].backgroundColor : [];
        } else if (Array.isArray(rawData) && rawData.length > 0) {
            rawData.forEach(function(item) {
                var label = item.nilai || item.label || 'N/A';
                var jumlah = item.jumlah || 0;
                labels.push(label);
                counts.push(jumlah);
                backgroundColors.push(randColorRGB());
            });
        }

        if (labels.length === 0) {
            if (_chartInstances[canvasId]) {
                _chartInstances[canvasId].destroy();
                delete _chartInstances[canvasId];
            }
            var canvasEl = $(`#${canvasId}`).get(0);
            if (canvasEl) {
                var ctx = canvasEl.getContext('2d');
                ctx.clearRect(0, 0, canvasEl.width, canvasEl.height);
                ctx.save();
                ctx.font = '14px sans-serif';
                ctx.fillStyle = '#888';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('Tidak ada data', canvasEl.width / 2, canvasEl.height / 2);
                ctx.restore();
            }
            return;
        }

        var canvasEl = $(`#${canvasId}`).get(0);
        if (!canvasEl) return;

        if (_chartInstances[canvasId]) {
            _chartInstances[canvasId].destroy();
            delete _chartInstances[canvasId];
        }

        var chartCanvas = canvasEl.getContext('2d');

        var defaultOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    enabled: true,
                },
            },
        };
        var options = { ...defaultOptions, ...chartOptions };

        _chartInstances[canvasId] = new Chart(chartCanvas, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah',
                    data: counts,
                    backgroundColor: backgroundColors,
                }],
            },
            options: options,
        });
    }
</script>
@push('css')
    <style nonce="{{ csp_nonce() }}">
        #barChart,
        #donutChart,
        #donutChart1,
        #donutChart2,
        #donutChart3 {
            min-height: 250px;
            height: 250px;
            max-height: 250px;
            max-width: 100%;
        }
    </style>
@endpush
