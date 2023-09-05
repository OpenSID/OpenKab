@extends('adminlte::page')

@section('footer')
    <strong>Hak cipta © <?= date('Y') ?> <a href="https://opendesa.id">OpenDesa</a>.</strong>
    Seluruh hak cipta dilindungi.
    <div class="float-right d-none d-sm-inline-block">
        <b>Versi</b> {{ openkab_versi() }}
    </div>
@endsection

@push('js')
    <script nonce="{{ csp_nonce() }}" type="application/javascript">
    document.addEventListener("DOMContentLoaded", function(event) {
        var base_url = '{{ url('/') }}';
        $.ajax({
        type: "get",
        url: base_url + "/api/v1/identitas",
        success: function (response) {
            var data = response.data.attributes;
            $('.brand-link').children('img').attr('alt', data.nama_aplikasi);
            $('.brand-link').children('span').text(data.nama_aplikasi);
        }
    });
        // ganti text navbar kecamatan dan desa
        var nama_kecamatan = $('#kecamatan').children().find('a.active').data('kecamatan');
        $('#kecamatan').children('a.active').text(nama_kecamatan);

        var nama_desa = $('#desa').children().find('a.active').data('desa');
        $('#desa').children('a.active').text(nama_desa);

        $.extend($.fn.dataTable.defaults, {
            language: {url: "{{ asset('vendor/datatable/id.json') }}"}
        });


        window.setTimeout(function() {
            $("#notifikasi").fadeTo(500, 0).slideUp(500, function() {
                $(this).remove();
            });
        }, 5000);


        function filter_open () {
            if ($('a[href="#collapse-filter"]').attr('aria-expanded') == 'false') {
                $('a[href="#collapse-filter"]').trigger('click')
            }
        }
    })
    </script>
@endpush
