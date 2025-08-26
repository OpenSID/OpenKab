<div class="row g-2">
    <div class="col-md-10">
        <div class="row g-2">
            <div class="col-md-4">
                {!! Html::select('search_kabupaten', [])->class('form-select border-0 py-3')->id('filter_kabupaten')->placeholder('-- Pilih Kabupaten --') !!}
            </div>
            <div class="col-md-4">
                {!! Html::select('search_kecamatan', [])->class('form-select border-0 py-3')->id('filter_kecamatan')->placeholder('-- Pilih Kecamatan --') !!}
            </div>
            <div class="col-md-4">
                {!! Html::select('search_desa', [])->class('form-select border-0 py-3')->id('filter_desa')->placeholder('-- Pilih Desa --') !!}
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <button class="btn btn-dark border-0 w-100 py-3" disabled id="search_button">Tampilkan</button>
    </div>
</div>
@include('components.wilayah_filter_js')
@push('scripts')
    @include('statistik.chart')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            $('select[name=search_desa]').change(function() {
                let _val = $(this).val()
                $('#search_button').prop('disabled', 1)
                if (_val) {
                    $('#search_button').prop('disabled', 0)
                }
            })

            $('#search_button').click(function() {
                let configDesa = $('select[name=search_desa]').val()
                $.ajax({
                    url: '{{ route('web.search') }}',
                    dataType: 'json',
                    data: {
                        config_desa: configDesa
                    },
                    beforeSend: function() {
                        $('#statistik_result').html(
                            'Mohon ditunggu, sedang mengambil data dari server ......')
                    },
                    success: function(data) {
                        $('#statistik_result').replaceWith(data.content)
                        $('#statistik_result li.list-group-item:first').addClass('active')
                        initializeDatatable($('#statistik_result li.list-group-item:first'))
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        $('#statistik_result').html(XMLHttpRequest.responseJSON['message'])
                    }
                })
            })

            $('body').on('click', '#statistik_result .panel-heading', function() {
                $(this).find('a > i').toggleClass('fa-angle-down fa-angle-up')
            })

            $('body').on('click', '#statistik_result .panel-collapse ul>li', function() {
                initializeDatatable($(this))
                $('#statistik_result .panel-collapse ul>li.active').removeClass('active')
                $(this).addClass('active')
            })

            function initializeDatatable(elm) {
                let kategori = $(elm).data('kategori')
                let default_id = $(elm).data('id')
                let config_desa = $(elm).data('configdesa')
                let tahun = new Date().getFullYear()
                let bulan = new Date().getMonth() + 1
                let exclude_chart = ['JUMLAH', 'BELUM MENGISI', 'TOTAL']
                $.ajax({
                    url: `{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web' }}/${kategori}?filter[id]=${default_id}&filter[tahun]=${tahun}&filter[bulan]=${bulan}&config_desa=${config_desa}`,
                    type: 'get',
                    dataType: 'json',
                    data: {},
                    beforeSend: function() {
                        $('#tabel-data tbody').html(
                            'Mohon ditunggu, sedang mengambil data dari server ......')
                    },
                    success: function(json) {
                        if (json.data.length > 0) {
                            data_grafik = [];
                            generateIsiTable('#tabel-data', elm, json.data)

                            json.data.forEach(function(item, index) {
                                if (!exclude_chart.includes(item.attributes.nama)) {
                                    data_grafik.push(item.attributes)
                                }

                            })

                            grafikPie()

                            return json.data;
                        }

                        return false;
                    },
                })
            }

            function generateIsiTable(tableSelector, elm, data) {
                let _table = $(tableSelector)
                let _tbody = _table.find('tbody')
                let _trs = []

                _table.find('thead #judul_kolom_nama').text($(elm).text())
                _tbody.empty()
                data.forEach((item, index) => {
                    _trs.push(`<tr>
                            <td>${index + 1}</td>
                            <td>${item.attributes.nama}</td>
                            <td>${item.attributes.jumlah}</td>
                            <td>${item.attributes.persentase_jumlah}</td>
                            <td>${item.attributes.laki_laki}</td>
                            <td>${item.attributes.persentase_laki_laki}</td>
                            <td>${item.attributes.perempuan}</td>
                            <td>${item.attributes.persentase_perempuan}</td>
                        </tr>`)
                })

                _tbody.append(_trs.join(''))
            }


        })
    </script>
@endpush

@push('styles')
    <style nonce="{{ csp_nonce() }}">
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
