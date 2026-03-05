@push('js')
    <script nonce="{{ csp_nonce() }}">
        $(function() {
            var statistik = [];
            var data_grafik = [];
            let exclude_chart = []    
            
            $('#bt_filter').click(function() {
                $('#summary_block').trigger('change');
                $('#statistik_block').trigger('change');                
            });

            $('#nav-statistik li a:first').addClass('active');
            $('#nav-statistik li').click(function(e) {
                e.preventDefault();
                $('#nav-statistik').find('li a.active').removeClass('active');
                $(this).find('a').addClass('active');

                $('#statistik thead').find('.judul').html($(this).find('a').data('name'))
                table.ajax.reload()
            });            

            $('#btn-grafik').click(function() {
                $(this).prop('disabled', true);
                // $('#btn-tabel').prop('disabled', false);
                $('#btn-pie').prop('disabled', false);

                $('#grafik').show();
                $('#pie').hide()
                // $('#statistik').hide()
            })

            $('#btn-pie').click(function() {
                $(this).prop('disabled', true);
                // $('#btn-tabel').prop('disabled', false);
                $('#btn-grafik').prop('disabled', false);

                $('#grafik').hide();
                $('#pie').show()
                // $('#statistik').hide()
            })


            var table = $('#statistik').DataTable({
                "processing": true,
                "serverSide": true,
                'pageLength': -1,
                "ordering": false,
                paging: false,
                searching: false,
                info: false,

                'order': [
                    [0, 'desc']
                ],

                "ajax": {
                    "url": "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/rtm' }}",
                    "type": "get",
                    "data": function(d) {
                        var nav = $('#nav-statistik').find('li a.active')
                        d['filter[id]'] = nav.data('key');
                        d['filter[tahun]'] = '';
                        d['kode_kabupaten'] = $("#filter_kabupaten").val();
                        d['kode_kecamatan'] = $("#filter_kecamatan").val();
                        d['kode_desa'] = $("#filter_desa").val();          
                        // d.config_desa = $('#position').val();
                    },
                    dataSrc: function(json) {
                        if (json.data.length > 0) {
                            data_grafik = [];
                            json.data.forEach(function(item, index) {
                                if (!exclude_chart.includes(item.attributes.nama)) {
                                    data_grafik.push(item.attributes)
                                }

                            });
                            grafikPie();

                        }
                        return json.data
                    },

                    error: function(xhr, status, error) {
                        // Handle the error callback here
                        console.error(error);
                    }
                },
                columns: [{
                        data: null,
                        sortable: false,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'attributes.nama',
                        sortable: false,
                        searchable: false,

                    },

                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {

                            return `<td class="text-center"><span class="mr-4">${data.attributes.jumlah}  </span>  ${data.attributes
                                .persentase_jumlah}</td>`;
                        }
                    },

                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return `<td class="text-center"><span class="mr-4">${data.attributes.laki_laki}  </span>  ${data.attributes
                                .persentase_laki_laki}</td>`;
                        }
                    },

                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return `<td class="text-center"><span class="mr-4">${data.attributes?.perempuan}  </span>  ${data.attributes
                                .persentase_perempuan}</td>`;
                        }
                    },


                ],
            });

            function grafikPie() {
                $('#barChart').remove();
                $('#donutChart').remove();

                var data = modifikasiData(data_grafik);
                $('#grafik').append('<canvas id="barChart"></canvas>');
                $('#pie').append('<canvas id="donutChart"></canvas>');
                tampilGrafik(data[0]);
                tampilPie(data[1]);
                $('#btn-grafik').click();
            }

            function tampilGrafik(areaChartData) {
                var barChartCanvas = $('#barChart').get(0).getContext('2d')
                var barChartData = $.extend(true, {}, areaChartData)
                var temp0 = areaChartData.datasets[0] ?? []
                var temp1 = areaChartData.datasets[1] ?? []
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

            function modifikasiData(data) {
                var dataBaruGrafik = []
                var labelsPie = [];
                var dataPie = [];
                var backgroundColorPie = [];

                data.forEach(function(item, index) {
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

            $('#statistik_block').change(function(){
                table.draw()
            })
        });
    </script>

    <script nonce="{{ csp_nonce() }}" type="text/javascript">
        document.addEventListener("DOMContentLoaded", function(event) {
            "use strict";

            const header = @include('layouts.components.header_bearer_api_gabungan');
            var urlDataWebsite = new URL("{{ config('app.databaseGabunganUrl') . '/api/v1/data-website' }}");

            $.ajax({
                url: urlDataWebsite.href,
                method: 'GET',
                dataType: 'json',
                headers: header,
                success: function(result) {
                    let category = result.data.categoriesItems;
                    let listDesa = result.data.listDesa;
                    let listKecamatan = result.data.listKecamatan;

                    for (let index in category) {
                        $(`.kategori-item .jumlah-${index}-elm`).text(category[index]['value']);
                    }

                    let _optionKecamatan = [];
                    let _optionDesa = [];

                    for (let item in listKecamatan) {
                        _optionKecamatan.push(`<option>${item}</option>`);
                    }

                    for (let item in listDesa) {
                        _optionDesa.push(`<optgroup label='${item}'>`);
                        for (let desa in listDesa[item]) {
                            _optionDesa.push(
                            `<option value='${desa}'>${listDesa[item][desa]}</option>`);
                        }
                        _optionDesa.push(`</optgroup>`);
                        _optionKecamatan.push(`<option>${item}</option>`);
                    }

                    $('select[name=search_kecamatan]').append(_optionKecamatan.join(''));
                    $('select[name=search_desa]').append(_optionDesa.join(''));
                }
            });
        });
    </script>
@endpush
