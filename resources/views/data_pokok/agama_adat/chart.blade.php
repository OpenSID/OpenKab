<script nonce="{{ csp_nonce() }}"  >
    function grafikBar() {
        data = [];
        
        // Hapus canvas jika sudah ada
        $('#barChart').remove();
        $('#grafik').append(
            '<canvas id="barChart"></canvas>'
        );

        // Data untuk bar chart
        tampilChart('bar', 'barChart', generateChartData(data_grafik_bar, 'jenis_tempat_ibadah', 'jumlah'));
    }

    function grafikPie() {
        data = [];
        
        // Hapus canvas jika sudah ada
        $('#donutChart1').remove();
        $('#donutChart2').remove();
        
        // Tambahkan canvas baru
        $('#pie1').append('<canvas id="donutChart1"></canvas>');
        $('#pie2').append('<canvas id="donutChart2"></canvas>');

        // Data untuk pie chart
        tampilChart('doughnut', 'donutChart1', generateChartData(data_grafik_pie, 'agama'));
        tampilChart('doughnut', 'donutChart2', generateChartData(data_grafik_pie, 'suku'));
    }
    function tampilChart(type, canvasId, chartData, chartOptions = {}) {
        var chartCanvas = $(`#${canvasId}`).get(0).getContext('2d');
        // Konfigurasi opsi default untuk chart
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
        // Gabungkan opsi default dengan opsi spesifik yang diberikan
        var options = { ...defaultOptions, ...chartOptions };
        // Membuat chart baru
        new Chart(chartCanvas, {
            type: type, // Tipe chart (bar, doughnut, dll.)
            data: {
                labels: chartData.labels, // Menggunakan labels dari data
                datasets: chartData.datasets, // Menggunakan datasets dari data
            },
            options: options,
        });
    }

    function generateChartData(data, labelKey, dataKey = null) { 
        var labels = [];
        var counts = [];
        var backgroundColors = [];
        var labelCounts = {}; // Hanya digunakan jika dataKey tidak ada

        // Jika dataKey tidak diberikan, hitung jumlah berdasarkan kemunculan labelKey
        if (!dataKey) {
            data.forEach(function (item) {
                var label = item[labelKey]; // Ambil label berdasarkan kunci label

                if (!labelCounts[label]) {
                    labelCounts[label] = 0;
                }
                labelCounts[label]++;
            });

            // Buat data untuk chart berdasarkan kemunculan unik
            Object.keys(labelCounts).forEach(function (label) {
                labels.push(label); // Tambahkan label
                counts.push(labelCounts[label]); // Jumlah kemunculan
                backgroundColors.push(randColorRGB()); // Tambahkan warna
            });
        } else {
            // Jika dataKey diberikan, gunakan nilai data dari dataKey
            data.forEach(function (item) {
                var label = item[labelKey]; // Ambil label berdasarkan kunci label
                var count = item[dataKey]; // Ambil nilai data berdasarkan kunci data
                
                // Pastikan label dan data tidak kosong
                if (label && count !== undefined) {
                    labels.push(label); // Tambahkan label
                    counts.push(count); // Tambahkan nilai
                    backgroundColors.push(randColorRGB()); // Tambahkan warna
                }
            });
        }

        // Struktur data chart
        return {
            labels: labels,
            datasets: [
                {
                    label: 'Jumlah',
                    data: counts,
                    backgroundColor: backgroundColors,
                },
            ],
        };
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
        #donutChart1, #donutChart2 {
            min-height: 250px;
            height: 250px;
            max-height: 250px;
            max-width: 100%;
        }
    </style>
@endpush