@extends('adminlte::page')

@section('footer')
    <strong>Hak cipta © <?= date('Y') ?> <a href="https://opendesa.id">OpenDesa</a>.</strong>
    Seluruh hak cipta dilindungi.
@endsection

@push('js')
    <script type="application/javascript">
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
