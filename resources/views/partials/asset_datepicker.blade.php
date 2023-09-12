
@push('js')
    <script src="{{ asset('vendor/moment/moment.js') }}"></script>
    <script src="{{ asset('vendor/moment/id.js') }}"></script>

    <script nonce="{{ csp_nonce() }}"  >
        document.addEventListener("DOMContentLoaded", function(event) {
            //Fortmat Tanggal dan Jam
            $('.datepicker').daterangepicker(
            {
                autoApply: true,
                format: "dd/mm/yyyy",
                singleDatePicker: true,
                locale: {
                    firstDay: 1
                }
            });

            $('.input-daterange').daterangepicker({
                autoApply: true,
                format: "dd/mm/yyyy",
                singleDatePicker: false,
                locale: {
                    firstDay: 1
                }
            });
        });
    </script>
@endpush
