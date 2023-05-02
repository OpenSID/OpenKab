@extends('adminlte::page')

@section('footer')
    <strong>Hak cipta © <?= date('Y') ?> <a href="https://opendesa.id">OpenDesa</a>.</strong>
    Seluruh hak cipta dilindungi.
    <div class="float-right d-none d-sm-inline-block">
        <b>Versi</b> {{ openkab_versi() }}
    </div>
@endsection

@push('js')
    <script type="application/javascript">
        $.extend($.fn.dataTable.defaults, {
            language: {url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"}
        });

        $(document).ready(function() {
            window.setTimeout(function() {
                $("#notifikasi").fadeTo(500, 0).slideUp(500, function() {
                    $(this).remove();
                });
            }, 5000);
        });

        function filter_open () {
            if ($('a[href="#collapse-filter"]').attr('aria-expanded') == 'false') {
                $('a[href="#collapse-filter"]').trigger('click')
            }
        }
    </script>
@endpush
