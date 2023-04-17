@extends('layouts.index')

@push('css')
@endpush

@section('title', 'Dasbor')

@section('content_header')
    <h1>Dasbor</h1>
@stop

@section('content')
    <x-adminlte-callout theme="warning">
        Selamat datang <b>{{ Auth::user()->username }}</b> di Dasbor Utama
        <b>{{ config('app.namaAplikasi') . ' ' . config('app.sebutanKab') . ' ' . config('app.namaKab') }}</b>.
    </x-adminlte-callout>

    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <x-adminlte-info-box id="penduduk" title="Penduduk" text="L : 2999 | P : 1999" icon="fas fa-lg fa-user"
                icon-theme="blue" />
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <x-adminlte-info-box id="keluarga" title="Keluarga" text="2991" icon="fas fa-lg fa-users" icon-theme="red" />
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <x-adminlte-info-box id="rtm" title="RTM" text="221" icon="fas fa-lg fa-home" icon-theme="green" />
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <x-adminlte-info-box id="bantuan" title="Bantuan" text="22" icon="fas fa-lg fa-handshake"
                icon-theme="yellow" />
        </div>
        <div class="col-12">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1"></i>
                        Statistik Penduduk
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="barChart"
                            style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block; width: 379px;"
                            width="758" height="500" class="chartjs-render-monitor"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            $.ajax({
                url: `{{ url('api/v1/dasbor') }}`,
                type: "GET",
                dataType: "json",
                success: function(response) {
                    jumlah = response.data;
                    $('#penduduk').find('.info-box-number').text('L : ' + jumlah
                        .jumlah_penduduk_laki_laki +
                        ' | P : ' + jumlah.jumlah_penduduk_perempuan);
                    $('#keluarga').find('.info-box-number').text(jumlah.jumlah_keluarga);
                    $('#rtm').find('.info-box-number').text(jumlah.jumlah_rtm);
                    $('#bantuan').find('.info-box-number').text(jumlah.jumlah_bantuan);
                }
            });

            var kategori = ['Januari 2022', 'Februari 2022', 'Maret 2022', 'April 2022', 'Mei 2022', 'Juni 2022',
                'Juli 2022', 'Agustus 2022', 'September 2022', 'Oktober 2022', 'November 2022', 'Desember 2022'
            ];

            var laki_laki = [65, 59, 80, 81, 56, 55, 40, 65, 59, 80, 81, 56];
            var perempuan = [28, 48, 40, 19, 86, 27, 90, 28, 48, 40, 19, 86];

            var areaChartData = {
                labels: kategori,
                datasets: [{
                        label: 'Perempuan',
                        backgroundColor: 'rgba(60,141,188,0.9)',
                        borderColor: 'rgba(60,141,188,0.8)',
                        pointRadius: false,
                        pointColor: '#3b8bba',
                        pointStrokeColor: 'rgba(60,141,188,1)',
                        pointHighlightFill: '#fff',
                        pointHighlightStroke: 'rgba(60,141,188,1)',
                        data: perempuan
                    },
                    {
                        label: 'Laki-laki',
                        backgroundColor: 'rgba(210, 214, 222, 1)',
                        borderColor: 'rgba(210, 214, 222, 1)',
                        pointRadius: false,
                        pointColor: 'rgba(210, 214, 222, 1)',
                        pointStrokeColor: '#c1c7d1',
                        pointHighlightFill: '#fff',
                        pointHighlightStroke: 'rgba(220,220,220,1)',
                        data: laki_laki
                    },
                ]
            }

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
        });
    </script>
@endpush
