@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Infrastruktur')

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">Statistik Kondisi Transportasi</div>
                <div class="card-body">
                    <div class="chart" id="grafik">
                        <canvas id="kondisiChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Statistik Sanitasi</div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="sanitasiChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <div class="float-left">Data Sarana dan Prasarana</div>
                </div>
                <div class="card-body">
                    <div id="infrastruktur"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')


<script nonce="{{ csp_nonce() }}">
document.addEventListener("DOMContentLoaded", function(event) {
   // Fungsi untuk mengambil data Komoditas dari API
   fetch('/api/v1/infrastruktur')
    .then(response => response.json())
    .then(data => {
        // Menampilkan data Komoditas di dalam div #komoditas-container
        const komoditasTable = document.createElement('table');
        komoditasTable.classList.add('table', 'table-bordered');
        komoditasTable.innerHTML = `
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Jenis Sarana/Prasarana</th>
                    <th>Kondisi Baik</th>
                    <th>Kondisi Rusak</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                </tr>
            </thead>
            <tbody>
                ${data.map(item => `
                    <tr>
                        <td>${item.kategori}</td>
                        <td>${item.jenis_sarana}</td>
                        <td>${item.kondisi_baik}</td>
                        <td>${item.kondisi_rusak}</td>
                        <td>${item.jumlah}</td>
                        <td>${item.satuan}</td>
                    </tr>
                `).join('')}
            </tbody>
        `;
        document.getElementById('infrastruktur').appendChild(komoditasTable);
    });
});

</script>

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

@endsection
@push('css')
    
<style nonce="{{ csp_nonce() }}" >
        #kondisiChart {
            min-height: 250px!important;
            height: 250px!important;
            max-height: 250px!important;
            max-width: 100%;
        }
        #sanitasiChart {
            min-height: 250px!important;
            height: 250px!important;
            max-height: 250px!important;
            max-width: 100%;
        }
    </style>
@endpush
