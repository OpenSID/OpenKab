<div class="col-12" id="statistik_penduduk_block">
    <div class="card card-success">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-pie mr-1"></i>
                Statistik Penduduk
            </h3>
        </div>
        <div class="card-body">
            <div class="chart">

            </div>
        </div>
    </div>
</div>

@push('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            $('#statistik_penduduk_block').change(function(event) {
                let header = @include('layouts.components.header_bearer_api_gabungan');
                var url = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/dasbor' }}");
                url.searchParams.set("kode_kabupaten", $("#filter_kabupaten").val());
                url.searchParams.set("kode_kecamatan", $("#filter_kecamatan").val());
                url.searchParams.set("kode_desa", $("#filter_desa").val());

                $.ajax({
                    url: url.href,
                    type: "GET",
                    headers: header,
                    dataType: "json",
                    success: function(response) {
                        res = response.data;
                        dataGrafik = res.grafik_penduduk;

                        grafik(dataGrafik);
                    }
                });
            });

            function grafik(dataGrafik) {
                rgb_l = randColorRGB();
                rgb_p = randColorRGB();
                hex_l = randColorHex();
                hex_p = randColorHex();

                var areaChartData = {
                    labels: dataGrafik.kategori,
                    datasets: [{
                            label: 'Perempuan',
                            backgroundColor: rgb_p,
                            borderColor: rgb_p,
                            pointRadius: false,
                            pointColor: hex_p,
                            pointStrokeColor: rgb_p,
                            pointHighlightFill: hex_p,
                            pointHighlightStroke: rgb_p,
                            data: dataGrafik.perempuan
                        },
                        {
                            label: 'Laki-laki',
                            backgroundColor: rgb_l,
                            borderColor: rgb_l,
                            pointRadius: false,
                            pointColor: rgb_l,
                            pointStrokeColor: hex_l,
                            pointHighlightFill: hex_l,
                            pointHighlightStroke: rgb_l,
                            data: dataGrafik.laki_laki
                        },
                    ]
                }
                if ($('#barChart').length > 0) {
                    $('#barChart').remove();
                }
                $('#statistik_penduduk_block .chart').html(
                    `<canvas id="barChart" width="758" height="500" class="chartjs-render-monitor"></canvas>`);
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
            }

            $('#statistik_penduduk_block').trigger('change');
        });
    </script>
@endpush
