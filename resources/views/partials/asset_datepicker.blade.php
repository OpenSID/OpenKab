@push('css')
    <link rel="stylesheet" href="{{ asset('assets/datepicker/bootstrap-datepicker.min.css') }}">
@endpush

@push('js')
    <script nonce="{{ csp_nonce() }}" src="{{ asset('assets/datepicker/bootstrap-datepicker.min.js') }}"></script>
    <script nonce="{{ csp_nonce() }}" src="{{ asset('assets/datepicker/bootstrap-datepicker.id.min.js') }}"></script>

    <script nonce="{{ csp_nonce() }}"  type="text/javscript">
        $(document).ready(function(){
            //Fortmat Tanggal dan Jam
            $('.datepicker').datepicker(
            {
                weekStart : 1,
                language:'id',
                format: 'dd-mm-yyyy',
                autoclose: true
            });

            $('.input-daterange').datepicker({
                language:'id',
                format: 'dd-mm-yyyy',
            });
        });
    </script>
@endpush
