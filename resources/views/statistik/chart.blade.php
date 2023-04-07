<script>
    function grafik_pie() {
        tampilkan_grafik(data_grafik)
        tampilkan_chart(data_grafik)
    }

    function tampilkan_grafik(areaChartData) {
        var areaChartData = modifikasi_data_grafik(areaChartData);
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

    function modifikasi_data_grafik(data) {
        var data_baru = []
        data.forEach(function(item, index) {
            let color = 'rgba(' + Math.floor(Math.random() * 255) + ',' + Math.floor(Math
                .random() * 255) + ',' + Math.floor(Math.random() * 255) + ', 1)'
            let colorPoint = '#' + Math.floor(Math.random() * 16777215).toString(16);

            data_baru.push({
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
        })

        return {
            labels: null,
            datasets: data_baru
        }
    }

    function tampilkan_chart(areaChartData) {
        var donutData = modifikasi_data_chart(areaChartData);
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

    function modifikasi_data_chart(chart) {
        var labels = [];
        var data = [];
        var backgroundColor = [];

        chart.forEach(function(item, index) {
            let color = '#' + Math.floor(Math.random() * 16777215).toString(16);

            labels.push(item.nama)
            data.push(item.jumlah)
            backgroundColor.push(color)
        })

        return {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColor,
            }]
        }
    }
</script>
