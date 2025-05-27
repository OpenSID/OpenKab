<script nonce="{{ csp_nonce() }}">

    function grafik() {
        $('#barChart').remove();

        var data = modifikasiData(data_grafik);
        $('#grafik').append('<canvas id="barChart"></canvas>');
        tampilGrafik(data[0]);
    }

    function tampilGrafik(areaChartData) {
        var barChartCanvas = $('#barChart').get(0).getContext('2d')
        var barChartData = $.extend(true, {}, areaChartData)
        var temp0 = areaChartData.datasets[0] ?? []
        var temp1 = areaChartData.datasets[1] ?? []
        barChartData.datasets[0] = temp1
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

    function modifikasiData(data) {
        var dataBaruGrafik = []
        var labelsPie = [];
        var dataPie = [];
        var backgroundColorPie = [];

        data.forEach(function(item, index) {
            let color = randColorRGB();
            let colorPoint = randColorHex();

            dataBaruGrafik.push({
                label: item.label,
                backgroundColor: color,
                borderColor: color,
                pointRadius: false,
                pointColor: color,
                pointStrokeColor: colorPoint,
                pointHighlightFill: colorPoint,
                pointHighlightStroke: color,
                data: [item.value, 1]
            })

            labelsPie.push(item.label)
            dataPie.push(item.label)
            backgroundColorPie.push(color)
        })

        return [{
                labels: [judul],
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
    <style nonce="{{ csp_nonce() }}" >
        #barChart {
            min-height: 250px;
            height: 250px;
            max-height: 250px;
            max-width: 100%;
        }
    </style>
@endpush