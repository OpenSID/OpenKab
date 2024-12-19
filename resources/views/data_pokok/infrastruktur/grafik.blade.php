<script>
    // Ambil data dari API
    fetch("{{ url('api/v1/infrastruktur') }}") // Ganti dengan rute API Anda
        .then(response => response.json())
        .then(data => {
            // Ambil data kondisi baik dan buruk
            const jalanBaik = data.find(item => item.jenis_sarana === 'Jalan Raya Aspal')?.kondisi_baik || 0;
            const jalanBuruk = data.find(item => item.jenis_sarana === 'Jalan Raya Aspal')?.kondisi_rusak || 0;

            const jembatanBaik = data.find(item => item.jenis_sarana === 'Jembatan Besi Beton')?.kondisi_baik || 0;
            const jembatanBuruk = data.find(item => item.jenis_sarana === 'Jembatan Besi Beton')?.kondisi_rusak || 0;


            // Konfigurasi Chart.js
            const ctx = document.getElementById('kondisiChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jalan Raya Aspal', 'Jembatan Besi Beton'],
                    datasets: [
                        {
                            label: 'Kondisi Baik',
                            data: [jalanBaik, jembatanBaik],
                            backgroundColor: '#4CAF50', // Hijau untuk kondisi baik
                            borderColor: '#388E3C',
                        },
                        {
                            label: 'Kondisi Buruk',
                            data: [jalanBuruk, jembatanBuruk],
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
        })
        .catch(error => console.error('Error fetching data:', error));
</script>