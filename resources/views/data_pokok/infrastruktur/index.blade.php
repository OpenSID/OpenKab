@extends('layouts.index')

@include('components.progressive-image')

@section('title', 'Data Infrastruktur')

@section('content_header')
    <h1>{{ $title }}</h1>
@stop

@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">Statistik Kondisi Transportasi</div>
                <div class="card-body">
                    <div>
                        <div class="chart" id="grafik">
                            <canvas id="barChart"></canvas>
                        </div>
                        <hr class="hr-chart">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">Statistik Sanitasi</div>
                <div class="card-body">
                    <div class="row">
                        <!-- Chart Kiri -->
                        <div class="col-md-6">
                            <div class="chart" id="pie1">
                                <canvas id="donutChart1"></canvas>
                            </div>
                        </div>
                        <!-- Chart Kanan -->
                        <div class="col-md-6">
                            <div class="chart" id="pie2">
                                <canvas id="donutChart2"></canvas>
                            </div>
                        </div>
                    </div>
                    <hr class="hr-chart">
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

@endsection
