@push('js')
    <script>
        $(function() {
            var statistik = [];
            var data_grafik = [];
            let exclude_chart = []

            $('#filter_kabupaten').select2({
                placeholder: "Pilih Kabupaten"
            });
            $('#filter_kecamatan').select2({
                placeholder: "Pilih Kecamatan"
            });
            $('#filter_desa').select2({
                placeholder: "Pilih Desa"
            });
            GetListKabupaten();
            GetListKecamatan();
            GetListDesa();
            $('#filter_kabupaten').on("select2:select", function(e) {
                GetListKecamatan(this.value);
            });
            $('#filter_kecamatan').on("select2:select", function(e) {
                GetListDesa(this.value);
            });
            $('#bt_clear_filter').click(function(){
                
                $("#filter_kabupaten").val("").trigger("change");
                $("#filter_kecamatan").val("").trigger("change");
                $("#filter_desa").val("").trigger("change");
                $('#filter_desa').empty().trigger("change");
                $('#bt_clear_filter').hide();
                table.ajax.reload();
            });
            $('#bt_filter').click(function(){
                $('#bt_clear_filter').show();
                table.ajax.reload();
            });

            $('#nav-statistik li a:first').addClass('active');
            $('#nav-statistik li').click(function(e) {
                e.preventDefault();
                $('#nav-statistik').find('li a.active').removeClass('active');
                $(this).find('a').addClass('active');

                $('#statistik thead').find('.judul').html($(this).find('a').data('name'))
                table.ajax.reload()
            });

            $('#btn-tabel').click(function() {
                $(this).prop('disabled', true);
                $('#btn-grafik').prop('disabled', false);
                $('#btn-pie').prop('disabled', false);

                $('#grafik').hide();
                $('#pie').hide()
                $('#statistik').show()
            })

            $('#btn-grafik').click(function() {
                $(this).prop('disabled', true);
                $('#btn-tabel').prop('disabled', false);
                $('#btn-pie').prop('disabled', false);

                $('#grafik').show();
                $('#pie').hide()
                $('#statistik').hide()
            })

            $('#btn-pie').click(function() {
                $(this).prop('disabled', true);
                $('#btn-tabel').prop('disabled', false);
                $('#btn-grafik').prop('disabled', false);

                $('#grafik').hide();
                $('#pie').show()
                $('#statistik').hide()
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
                    "url": "{{ url('api/v1/statistik-web/rtm') }}",
                    "type": "get",
                    "data": function(d) {
                        var nav = $('#nav-statistik').find('li a.active')
                        d['filter[id]'] = nav.data('key');
                        d['filter[tahun]'] = '';
                        d['filter[kabupaten]'] = $("#filter_kabupaten").val();
                        d['filter[kecamatan]'] = $("#filter_kecamatan").val();
                        d['filter[desa]'] = $("#filter_desa").val();
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

            function GetListKabupaten() {
            $('#filter_kabupaten').empty().trigger("change");
            var optionEmpty = new Option("", "");
            $("#filter_kabupaten").append(optionEmpty);
            $.ajax({
                type: 'GET',
                url: "{{ url('api/v1/statistik-web/get-list-kabupaten') }}",
                dataType: 'json',
                success: function(data) {
                    for (var i = 0; i < data.length; i++) {
                        var newOption = new Option(data[i].nama_kabupaten, data[i].kode_kabupaten, true, true);
                        $("#filter_kabupaten").append(newOption);
                    }
                    $("#filter_kabupaten").val("").trigger("change");
                },
                error: function(jqXHR, textStatus, errorThrown) {
                }
            });
        }
        function GetListKecamatan($id) {
            $('#filter_kecamatan').empty().trigger("change");
            if ($id != undefined && $id != null){
                var optionEmpty = new Option("", "");
                $("#filter_kecamatan").append(optionEmpty);
                $.ajax({
                    type: 'GET',
                    url: "{{ url('api/v1/statistik-web/get-list-kecamatan') }}" + "/" + $id,
                    dataType: 'json',
                    success: function(data) {
                        for (var i = 0; i < data.length; i++) {
                            var newOption = new Option(data[i].nama_kecamatan, data[i].kode_kecamatan, true, true);
                            $("#filter_kecamatan").append(newOption);
                        }
                        $("#filter_kecamatan").val("");
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                    }
                });
            }
        }
        function GetListDesa($id) {
            $('#filter_desa').empty().trigger("change");
            if ($id != undefined && $id != null){
                var optionEmpty = new Option("", "");
                $("#filter_desa").append(optionEmpty);
                $.ajax({
                    type: 'GET',
                    url: "{{ url('api/v1/statistik-web/get-list-desa') }}" + "/" + $id,
                    dataType: 'json',
                    success: function(data) {
                        for (var i = 0; i < data.length; i++) {
                            var newOption = new Option(data[i].nama_desa, data[i].kode_desa, true, true);
                            $("#filter_desa").append(newOption);
                        }
                        $("#filter_desa").val("");
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                    }
                });
            }
        }

            function grafikPie() {
                $('#barChart').remove();
                $('#donutChart').remove();

                var data = modifikasiData(data_grafik);
                $('#grafik').append('<canvas id="barChart"></canvas>');
                $('#pie').append('<canvas id="donutChart"></canvas>');
                tampilGrafik(data[0]);
                tampilPie(data[1]);
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
        });
    </script>

    <script nonce="{{ csp_nonce() }}" type="text/javascript">
        document.addEventListener("DOMContentLoaded", function(event) {
            "use strict";
            $.get('{{ url('index.php/api/v1/data-website') }}', {}, function(result) {
                let category = result.data.categoriesItems
                let listDesa = result.data.listDesa
                let listKecamatan = result.data.listKecamatan

                for (let index in category) {
                    $(`.kategori-item .jumlah-${index}-elm`).text(category[index]['value'])
                };
                let _optionKecamatan = []
                let _optionDesa = []
                for (let item in listKecamatan) {
                    _optionKecamatan.push(`<option>${item}</option>`)
                }

                for (let item in listDesa) {
                    _optionDesa.push(`<optgroup label='${item}'>`)
                    for (let desa in listDesa[item]) {
                        _optionDesa.push(`<option value='${desa}'>${listDesa[item][desa]}</option>`)
                    }
                    _optionDesa.push(`</optgroup>`)
                    _optionKecamatan.push(`<option>${item}</option>`)
                }

                $('select[name=search_kecamatan]').append(_optionKecamatan.join(''))
                $('select[name=search_desa]').append(_optionDesa.join(''))
            }, 'json')
        });
    </script>
@endpush