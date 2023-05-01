@push('css')
    <link rel="stylesheet" href="{{ asset('assets/datepicker/bootstrap-datepicker.min.css') }}">
@endpush

@push('js')
    <script src="{{ asset('assets/datepicker/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('assets/datepicker/bootstrap-datepicker.id.min.js') }}"></script>

    <script>
        $(document).ready(function(){
            //Fortmat Tanggal dan Jam
            $('.datepicker').datepicker(
            {
                weekStart : 1,
                language:'id',
                format: 'dd-mm-yyyy',
                autoclose: true
            });
        });
    </script>
@endpush