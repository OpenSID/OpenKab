<script nonce="{{ csp_nonce() }}"  >
    function randColorRGB() {
        const r = Math.floor(Math.random() * 200) + 55;
        const g = Math.floor(Math.random() * 200) + 55;
        const b = Math.floor(Math.random() * 200) + 55;
        return `rgba(${r}, ${g}, ${b}, 0.7)`;
    }

    function randColorHex() {
        const r = Math.floor(Math.random() * 200) + 55;
        const g = Math.floor(Math.random() * 200) + 55;
        const b = Math.floor(Math.random() * 200) + 55;
        return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
    }

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
        var grafik = modifikasiDataGrafik(data_grafik);
        var pie = modifikasiDataPie(data_grafik, 'tingkat_pemanfaatan');

        tampilGrafik(grafik[0]);
        tampilPie(pie);
    }

    function tampilGrafik(areaChartData) {
        var barChartCanvas = $('#barChart').get(0).getContext('2d')
        
        var barChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }

        new Chart(barChartCanvas, {
            type: 'bar',
            data: areaChartData,
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

    function modifikasiDataGrafik(data) {
        var dataBaruGrafik = []
        var labelsPie = [];
        var dataPie = [];
        var backgroundColorPie = [];
        var labels = [];

        data.forEach(function(item, index) {
            let color = randColorRGB();
            let colorPoint = randColorHex();

            let jumlah = typeof item.jumlah_penginapan == 'string' ? parseInt(item.jumlah_penginapan) : 0

            labels.push(item.jenis_hiburan)
            dataBaruGrafik.push({
                label: item.jenis_hiburan,
                backgroundColor: color,
                borderColor: color,
                borderWidth: 1,
                data: [jumlah]
            })

            labelsPie.push(item.jenis_hiburan)
            dataPie.push(jumlah)
            backgroundColorPie.push(color)
        })

        return [{
                labels: labels,
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

    function modifikasiDataPie(data, key) {
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
        #donutChart {
            min-height: 250px;
            height: 250px;
            max-height: 250px;
            max-width: 100%;
        }
    </style>
@endpush
