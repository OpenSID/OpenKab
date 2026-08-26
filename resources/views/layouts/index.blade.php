@extends('adminlte::page')
@section('meta_tags')
<meta name="identitas-openkab" content="{{ str_replace('.','',$identitasAplikasi['kode_kabupaten'] ?? '') }}">
@endsection
@section('footer')
<strong>Hak cipta © <?= date('Y') ?> <a href="https://opendesa.id">OpenDesa</a>.</strong>
Seluruh hak cipta dilindungi.
<div class="float-right d-none d-sm-inline-block">
    <b>Versi</b> {{ openkab_versi() }}
</div>
@endsection

@push('js')
<script nonce="{{ csp_nonce() }}" type="application/javascript">
    function randColorRGB() {
        const r = Math.floor(Math.random() * 200) + 55;
        const g = Math.floor(Math.random() * 200) + 55;
        const b = Math.floor(Math.random() * 200) + 55;
        return `rgba(${r}, ${g}, ${b}, 0.7)`;
    }

    const API_PROXY_BASE = '/api-proxy/get';

    function apiProxyGet(endpoint, params, callback) {
        let url = API_PROXY_BASE + '?endpoint=' + endpoint;
        for (let key in params) {
            if (params[key] !== null && params[key] !== '') {
                url += '&' + encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
            }
        }

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (callback) callback(response);
            },
            error: function(response) {
                if (callback) callback({
                    data: [],
                    meta: {
                        pagination: {
                            total: 0
                        }
                    }
                });

                Swal.fire(
                    'Error!',
                    response.responseJSON.error || 'Gagal mengambil data dari API',
                    'error'
                );
            }
        });
    }
    document.addEventListener("DOMContentLoaded", function(event) {
        $.ajax({
            type: "get",
            url: "/api/v1/identitas",
            success: function(response) {
                var data = response.data.attributes;
                $('.brand-link').children('img').attr('alt', data.nama_aplikasi);
                $('.brand-link').children('span').text(data.nama_aplikasi);
            }
        });
        // ganti text navbar kabupaten, kecamatan dan desa
        var nama_kabupaten = $('#kabupaten').children().find('a.active').data('kabupaten');
        $('#kabupaten').children('a.active').text(nama_kabupaten);

        var nama_kecamatan = $('#kecamatan').children().find('a.active').data('kecamatan');
        $('#kecamatan').children('a.active').text(nama_kecamatan);

        var nama_desa = $('#desa').children().find('a.active').data('desa');
        $('#desa').children('a.active').text(nama_desa);

        $.extend($.fn.dataTable.defaults, {
            language: {
                url: "{{ asset('vendor/datatable/id.json') }}"
            },
            searchDelay: 500,
        });

        $(document).on('init.dt', function(e, settings) {
            if (e.namespace !== 'dt') return;

            var table = new $.fn.dataTable.Api(settings);
            var searchDelay = table.init().searchDelay || 500;
            var searchInput = $('div.dataTables_filter input', table.table().container());
            var debounceTimer = null;
            var previousSearch = null;

            searchInput.off('keyup.DT input.DT search.DT keydown.DT');

            searchInput.on('keyup input', function() {
                var currentValue = this.value;

                if (previousSearch === currentValue) return;

                previousSearch = currentValue;

                clearTimeout(debounceTimer);

                debounceTimer = setTimeout(function() {
                    if (table.search() !== currentValue) {
                        table.search(currentValue).draw();
                    }
                }, searchDelay);
            });
        });


        window.setTimeout(function() {
            $("#notifikasi").fadeTo(500, 0).slideUp(500, function() {
                $(this).remove();
            });
        }, 5000);


        function filter_open() {
            if ($('a[href="#collapse-filter"]').attr('aria-expanded') == 'false') {
                $('a[href="#collapse-filter"]').trigger('click')
            }
        }

        $('li#catatan-rilis').click(function() {
            Swal.fire({
                title: 'Menyimpan',
                didOpen: () => {
                    Swal.showLoading()
                },
            })
            $.get('/catatan-rilis', {}, function(data) {
                Swal.fire({
                    title: 'Catatan Rilis',
                    width: '90%',
                    html: data,
                    position: 'top',
                    confirmButtonText: 'Tutup',
                    showConfirmButton: false,
                    showCloseButton: true,
                    focusConfirm: false,
                    customClass: {
                        htmlContainer: 'text-left'
                    }

                })
            })
        })

        $('.datepicker').daterangepicker({
            autoApply: true,
            singleDatePicker: true,
            locale: {
                format: "DD/MM/YYYY",
                firstDay: 1
            }
        });
    })
</script>
@endpush
