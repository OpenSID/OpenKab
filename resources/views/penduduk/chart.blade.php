<script nonce="{{ csp_nonce() }}"  >
    // function grafik() {
    //     data = [];
    //     $('#barChart').remove();
    //     // $('#donutChart').remove();
    //     $('#grafik').append(
    //         '<canvas id="barChart"></canvas>'
    //     );
    //     // $('#pie').append(
    //     //     '<canvas id="donutChart"></canvas>'
    //     // );
    //     // Data untuk bar chart
    //     tampilChart('bar', 'barChart', generateChartData(data_grafik, "{{ isset($chart) ? $chart['chart'] : 'umur' }}"));
    //     // Data untuk pie chart
    //     // tampilChart('doughnut', 'donutChart', generateChartData(data_grafik, 'umur'));
    // }

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
                label: item.umur,
                backgroundColor: color,
                borderColor: color,
                pointRadius: false,
                pointColor: color,
                pointStrokeColor: colorPoint,
                pointHighlightFill: colorPoint,
                pointHighlightStroke: color,
                data: [item.umur, 1]
            })

            labelsPie.push(item.nama)
            dataPie.push(item.jumlah)
            backgroundColorPie.push(color)
        })

        return [{
                labels: ['Rentang Umur'],
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
    
    // function tampilChart(type, canvasId, chartData, chartOptions = {}) {
    //     var chartCanvas = $(`#${canvasId}`).get(0).getContext('2d');
    //     // Konfigurasi opsi default untuk chart
    //     var defaultOptions = {
    //         responsive: true,
    //         maintainAspectRatio: false,
    //         plugins: {
    //             legend: {
    //                 display: true,
    //                 position: 'top',
    //             },
    //             tooltip: {
    //                 enabled: true,
    //             },
    //         },
    //     };
    //     // Gabungkan opsi default dengan opsi spesifik yang diberikan
    //     var options = { ...defaultOptions, ...chartOptions };
    //     // Membuat chart baru
    //     new Chart(chartCanvas, {
    //         type: type, // Tipe chart (bar, doughnut, dll.)
    //         data: {
    //             labels: chartData.labels, // Menggunakan labels dari data
    //             datasets: chartData.datasets, // Menggunakan datasets dari data
    //         },
    //         options: options,
    //     });
    // }
    // function generateChartData(data, key) {
    //     var labelCounts = {}; // Objek untuk menghitung jumlah label unik
    //     var labels = [];
    //     var counts = [];
    //     var backgroundColors = [];
    //     // Hitung jumlah label unik berdasarkan kunci yang diberikan
    //     data.forEach(function (item) {
    //         var value = item[key]; // Ambil nilai berdasarkan kunci
    //         if (!labelCounts[value]) {
    //             labelCounts[value] = 0;
    //         }
    //         labelCounts[value]++;
    //     });
    //     // Buat data untuk chart
    //     Object.keys(labelCounts).forEach(function (label) {
    //         let color = randColorRGB();
    //         labels.push(label); // Tambahkan label
    //         counts.push(labelCounts[label]); // Tambahkan jumlah
    //         backgroundColors.push(color); // Tambahkan warna
    //     });
    //     // Struktur data chart

    //     return {
    //         labels: labels,
    //         datasets: [
    //             {
    //                 label: "{{ isset($chart) ? $chart['header'] : 'Data' }}",
    //                 data: counts,
    //                 backgroundColor: backgroundColors,
    //             },
    //         ],
    //     };
    // }
</script>
@push('css')
    <style nonce="{{ csp_nonce() }}" >
        #barChart {
            min-height: 250px;
            height: 250px;
            max-height: 250px;
            max-width: 100%;
        }
        /* #donutChart {
            min-height: 250px;
            height: 250px;
            max-height: 250px;
            max-width: 100%;
        } */
    </style>
@endpush