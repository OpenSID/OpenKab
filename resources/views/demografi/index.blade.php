@extends('layouts.index')

@section('plugins.chart', true)

@section('title', 'Dasbor Demografi')


@section('content')
    @include('partials.breadcrumbs')
    <div class="row">
        <div class="card rounded-0 border-0 shadow-none col-12">
            <div class="card-body">
                @include('demografi.filter')
                <div class="row">
                    @include('demografi.statistik_penduduk')
                </div>
                <div class="row">
                    @foreach ($chartItems as $chart)
                        @include('demografi.chart_item', ['chart' => $chart])
                    @endforeach
                </div>
            </div>
        </div>
    </div>

@endsection
@push('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            $('li#kabupaten').remove();
            $('li#kecamatan').remove();
            $('li#desa').remove();

            $('#bt_filter').click(function() {
                $('.card-chart').trigger('change');
                $('#statistik_penduduk_block').trigger('change');
            });

            $('.card-chart').change(function(event) {
                let header = @include('layouts.components.header_bearer_api_gabungan');
                let chartContent = $(this).find('.chart_content');
                let urlChart = chartContent.data('url');
                let dataGrafik = [],
                    chartData = [],
                    res = [];
                var url = new URL(`{{ config('app.databaseGabunganUrl') }}/${urlChart}`);
                url.searchParams.set("kode_kabupaten", $("#filter_kabupaten").val());
                url.searchParams.set("kode_kecamatan", $("#filter_kecamatan").val());
                url.searchParams.set("kode_desa", $("#filter_desa").val());

                $.ajax({
                    url: url.href,
                    type: "GET",
                    beforeSend: function(xhr) {
                        chartContent.html('Sedang memuat data...');
                    },
                    headers: header,
                    dataType: "json",
                    success: function(response) {
                        res = response.data;
                        dataGrafik = [];
                        res.forEach(function(item, index) {
                            dataGrafik.push(item.attributes)
                        })
                        chartContent.empty();
                        chartContent.html(
                            `<canvas id="donutChart-${chartContent.data('key')}"></canvas>`);
                        chartData = modifikasiData(dataGrafik);

                        tampilPie(`#donutChart-${chartContent.data('key')}`, chartData[1])
                    }
                });
            });

            $('.card-chart').trigger('change');

            function tampilPie(target, donutData) {
                var donutChartCanvas = $(target).get(0).getContext('2d')
                var donutOptions = {
                    maintainAspectRatio: false,
                    responsive: true,
                    legend: {
                        display: false,
                    },
                }
                new Chart(donutChartCanvas, {
                    type: 'doughnut',
                    data: donutData,
                    options: donutOptions
                })
            }

            function modifikasiData(data) {
                var dataBaruGrafik = []
                var labelsPie = [];
                var dataPie = [];
                var backgroundColorPie = [];

                data.forEach(function(item, index) {
                    if (item.nama == 'TOTAL') {
                        // Skip item with nama 'TOTAL'
                        return;
                    }
                    let color = randColorRGB();
                    let colorPoint = randColorHex();

                    dataBaruGrafik.push({
                        label: item.nama,
                        backgroundColor: color,
                        borderColor: color,
                        pointRadius: false,
                        pointColor: color,
                        pointStrokeColor: colorPoint,
                        pointHighlightFill: colorPoint,
                        pointHighlightStroke: color,
                        data: [item.jumlah, 1]
                    })

                    labelsPie.push(item.nama)
                    dataPie.push(item.jumlah)
                    backgroundColorPie.push(color)
                })

                return [{
                        labels: ['Data'],
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
        })
    </script>
@endpush
@push('css')
    <style nonce="{{ csp_nonce() }}">
        #donutChart {
            min-height: 250px;
            height: 450px;
            max-height: 450px;
            max-width: 100%;
        }

        .card-chart {
            min-height: 300px;
        }
    </style>
@endpush
