<div class="row g-2">
    <div class="col-md-10">
        <div class="row g-2">
            <div class="col-md-6">
                {!! Form::select('search_kecamatan', $listKecamatan, null, ['class' => 'form-select border-0 py-3']) !!}
            </div>
            <div class="col-md-6">
                {!! Form::select('search_desa', $listDesa, null, ['class' => 'form-select border-0 py-3']) !!}
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <button class="btn btn-dark border-0 w-100 py-3" disabled id="search_button">Tampilkan</button>
    </div>
</div>

@push('scripts')
    @include('statistik.chart')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function (event) {
            $('select[name=search_desa]').find('optgroup').hide()
            $('select[name=search_kecamatan]').change(function(){
                let _val = $(this).val()
                $('select[name=search_desa]').find('optgroup').hide()
                $('select[name=search_desa]').find('optgroup[label="'+_val+'"]').show()
                $('select[name=search_desa]').find('optgroup[label="'+_val+'"]').find('option:first').prop('selected', 1)
                $('select[name=search_desa]').trigger('change')
            })

            $('select[name=search_desa]').change(function(){
                let _val = $(this).val()
                $('#search_button').prop('disabled', 1)
                if (_val) {
                    $('#search_button').prop('disabled', 0)
                }
            })

            $('#search_button').click(function(){
                let configDesa = $('select[name=search_desa]').val()
                $.ajax({
                    url: '{{ route('web.search') }}',
                    dataType: 'json',
                    data: { config_desa:  configDesa},
                    beforeSend: function(){
                        $('#statistik_result').html('Mohon ditunggu, sedang mengambil data dari server ......')
                    },
                    success: function(data){
                        $('#statistik_result').replaceWith(data.content)
                        initializeDatatable()
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        $('#statistik_result').html(XMLHttpRequest.responseJSON['message'])
                    }
                })
            })

            $('body').on('click', '#statistik_result .panel-heading', function(){
                $(this).find('i').toggleClass('fa-angle-down fa-angle-up')
            })

            function initializeDatatable(){
                let kategori = $('#statistik_result li.list-group-item:first').data('kategori')
                let default_id = $('#statistik_result li.list-group-item:first').data('id')
                let config_desa = $('#statistik_result li.list-group-item:first').data('configdesa')
                var statistik = $('#tabel-data').DataTable({
                    processing: true,
                    serverSide: true,
                    autoWidth: false,
                    ordering: false,
                    searching: false,
                    paging: false,
                    info: false,
                    ajax: {
                        url: `{{ url('api/v1/statistik') }}/${kategori}?filter[id]=${default_id}&filter[configdesa]=${config_desa}`,
                        method: 'get',
                        data: function(row) {
                            return {
                                "filter[bulan]": $("#bulan").val(),
                                "filter[tahun]": $("#tahun").val(),
                            };
                        },
                        dataSrc: function(json) {
                            if (json.data.length > 0) {
                                data_grafik = [];
                                json.data.forEach(function(item, index) {
                                    data_grafik.push(item.attributes)
                                })

                                grafikPie()

                                return json.data;
                            }

                            return false;
                        },
                    },
                    columnDefs: [{
                            targets: '_all',
                            className: 'text-nowrap',
                        },
                        {
                            targets: [2, 3, 4, 5, 6, 7],
                            className: 'dt-body-right',
                        },
                    ],
                    columns: [{
                        data: null,
                    }, {
                        data: function(data) {
                            return data.attributes.nama;
                        },
                    }, {
                        data: function(data) {
                            return data.attributes.jumlah
                        },
                    }, {
                        data: function(data) {
                            return data.attributes.persentase_jumlah;
                        },
                    }, {
                        data: function(data) {
                            return data.attributes.laki_laki
                        },
                    }, {
                        data: function(data) {
                            return data.attributes.persentase_laki_laki;
                        },
                    }, {
                        data: function(data) {
                            return data.attributes.perempuan
                        },
                    }, {
                        data: function(data) {
                            return data.attributes.persentase_perempuan;
                        },
                    }]
                });

                statistik.on('draw.dt', function() {
                    var dataTable = $('#tabel-data').DataTable();
                    var pageInfo = dataTable.page.info();
                    var recordsTotal = dataTable.data().count();

                    statistik.column(0, {
                        page: 'current'
                    }).nodes().each(function(cell, i) {
                        if ((recordsTotal - i) <= 3) {
                            cell.innerHTML = '';
                        } else {
                            cell.innerHTML = i + 1 + pageInfo.start;
                        }
                    });
                });

            }
        })
    </script>
@endpush
