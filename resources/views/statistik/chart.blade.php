<script>
    function grafikPie() {
        data = [];
        $('#barChart').remove();
        $('#donutChart').remove();
        $('#grafik').append(
            '<canvas id="barChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>'
        );
        $('#pie').append(
            '<canvas id="donutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>'
        );
        var data = modifikasiData(data_grafik);
        tampilGrafik(data[0]);
        tampilPie(data[1]);

        console.log(data[0]);
    }

    function tampilGrafik(areaChartData) {
        var barChartCanvas = $('#barChart').get(0).getContext('2d')
        var barChartData = $.extend(true, {}, areaChartData)
        var temp0 = areaChartData.datasets[0]
        var temp1 = areaChartData.datasets[1]
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
            let color = 'rgba(' + Math.floor(Math.random() * 255) + ',' + Math.floor(Math
                .random() * 255) + ',' + Math.floor(Math.random() * 255) + ', 1)'
            let colorPoint = '#' + Math.floor(Math.random() * 16777215).toString(16);

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
                labels: null,
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
