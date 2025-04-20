<script nonce="{{ csp_nonce() }}"  >
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
        // Data untuk bar chart
        tampilChart('bar', 'barChart', generateChartData(data_grafik, 'jenis_lahan'));
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
    function generateChartData(data, key, label = 'Jenis Lahan') {
        var labelCounts = {}; // Objek untuk menghitung jumlah label unik
        var labels = [];
        var counts = [];
        var backgroundColors = [];
        // Hitung jumlah label unik berdasarkan kunci yang diberikan
        data.forEach(function (item) {
            var value = item[key]; // Ambil nilai berdasarkan kunci
            if (!labelCounts[value]) {
                labelCounts[value] = 0;
            }
            labelCounts[value]++;
        });
        // Buat data untuk chart
        Object.keys(labelCounts).forEach(function (label) {
            let color = randColorRGB();
            labels.push(label); // Tambahkan label
            counts.push(labelCounts[label]); // Tambahkan jumlah
            backgroundColors.push(color); // Tambahkan warna
        });
        // Struktur data chart
        return {
            labels: labels,
            datasets: [
                {
                    label: label,
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
        #donutChart {
            min-height: 250px;
            height: 250px;
            max-height: 250px;
            max-width: 100%;
        }
    </style>
@endpush