<script nonce="{{ csp_nonce() }}">
    function grafikPie() {
        data = [];
        $('#barChart').remove();
        $('#donutChart').remove();
        $('#grafik').append(
            '<canvas id="barChart"></canvas>'
        );
        $('#pie').append(
            '<canvas id="donutChart"></canvas>'
        );
        var data = modifikasiData(data_grafik);
        tampilGrafik(data[0]);
        tampilPie(data[1]);
    }

    function tampilGrafik(areaChartData) {
        var barChartCanvas = $('#barChart').get(0).getContext('2d')
        var barChartData = $.extend(true, {}, areaChartData)
        var temp0 = areaChartData.datasets[0]
        var temp1 = areaChartData.datasets[1]
        barChartData.datasets[0] = temp1 ?? {}
        barChartData.datasets[1] = temp0

        var barChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            datasetFill: false
        }

        new Chart(barChartCanvas, {
            type: 'bar',
            data: barChartData,
            options: barChartOptions
        })
    }

    function tampilPie(donutData) {
        var donutChartCanvas = $('#donutChart').get(0).getContext('2d')
        var donutOptions = {
            maintainAspectRatio: false,
            responsive: true,
        }
        new Chart(donutChartCanvas, {
            type: 'doughnut',
            data: donutData,
            options: donutOptions
        })
    }

    function modifikasiData(data) {
        var dataBaruGrafik = []
        var labelsPie = [];
        var dataPie = [];
        var backgroundColorPie = [];

        data.forEach(function(item, index) {
            let color = randColorRGB();
            let colorPoint = randColorHex();
            if (item.nama == 'TOTAL') {
                // Skip item with nama 'TOTAL'
                return;
            }
            if (item.nama == 'JUMLAH') {
                // Skip item with nama 'JUMLAH'
                return;
            }
            dataBaruGrafik.push({
                label: item.nama,
                backgroundColor: color,
                borderColor: color,
                pointRadius: false,
                pointColor: color,
                pointStrokeColor: colorPoint,
                pointHighlightFill: colorPoint,
                pointHighlightStroke: color,
                data: [item.jumlah, 1]
            })

            labelsPie.push(item.nama)
            dataPie.push(item.jumlah)
            backgroundColorPie.push(color)
        })

        return [{
                labels: ['Data'],
                datasets: dataBaruGrafik
            },
            {
                labels: labelsPie,
                datasets: [{
                    data: dataPie,
                    backgroundColor: backgroundColorPie,
                }]
            }
        ]
    }
</script>
@push('css')
    <style nonce="{{ csp_nonce() }}">
        #barChart {
            min-height: 250px;
            height: 250px;
            max-height: 250px;
            max-width: 100%;
        }

        #donutChart {
            min-height: 250px;
            height: 250px;
            max-height: 250px;
            max-width: 100%;
        }
    </style>
@endpush
