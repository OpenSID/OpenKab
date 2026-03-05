<script nonce="{{ csp_nonce() }}">
    function grafik(chartData) {

        // Data untuk bar chart
        tampilKondisiChart(chartData);
        tampilkanSanitasiChart(chartData);
        console.log(chartData)
    }

    function tampilKondisiChart(chartData, chartOptions = {}) {
        // Konfigurasi Chart.js
        const ctx = document.getElementById('kondisiChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jalan Raya Aspal', 'Jembatan Besi Beton'],
                datasets: [{
                        label: 'Kondisi Baik',
                        data: [chartData.jalanBaik, chartData.jembatanBaik],
                        backgroundColor: '#4CAF50', // Hijau untuk kondisi baik
                        borderColor: '#388E3C',
                    },
                    {
                        label: 'Kondisi Buruk',
                        data: [chartData.jalanBuruk, chartData.jembatanBuruk],
                        backgroundColor: '#F44336', // Merah untuk kondisi buruk
                        borderColor: '#D32F2F',
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: true
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: true
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    }

    function tampilkanSanitasiChart(chartData, chartOptions = {}) {
        const ctx = document.getElementById('sanitasiChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Sumur Resapan', 'MCK Umum'],
                datasets: [{
                    data: [chartData.sumurResapan, chartData.mckUmum],
                    backgroundColor: ['#4caf50', '#ffc107'], // Warna untuk pie chart
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                return `${label}: ${value} Unit`;
                            }
                        }
                    }
                }
            }
        });
    }
</script>