<script>
    // Ambil data dari API
    fetch("{{ url('api/v1/infrastruktur') }}") // Ganti route dengan rute API Anda
        .then(response => response.json())
        .then(data => {
            // Cari data Sanitasi (MCK Umum dan Sumur Resapan)
            const sumurResapan = data.find(item => item.jenis_sarana === 'Sumur Resapan')?.jumlah || 0;
            const mckUmum = data.find(item => item.jenis_sarana === 'MCK Umum')?.jumlah || 0;

            // Buat pie chart menggunakan Chart.js
            const ctx = document.getElementById('sanitasiChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Sumur Resapan', 'MCK Umum'],
                    datasets: [{
                        data: [sumurResapan, mckUmum],
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
        })
        .catch(error => console.error('Error fetching data:', error));
</script>