
@push('js')
    <script src="{{ asset('vendor/moment/moment.js') }}"></script>
    <script src="{{ asset('vendor/moment/id.js') }}"></script>

    <script nonce="{{ csp_nonce() }}"  >
        document.addEventListener("DOMContentLoaded", function(event) {
            //Fortmat Tanggal dan Jam
            $('.datepicker').daterangepicker(
            {
                autoApply: true,
                singleDatePicker: true,
                locale: {
                    format: "{{ config('app.format.date_js') }}",
                    firstDay: 1
                }
            });

            $('.input-daterange').daterangepicker({
                autoApply: true,
                singleDatePicker: false,
                locale: {
                    format: "{{ config('app.format.date_js') }}",
                    firstDay: 1
                }
            });
        });
    </script>
@endpush
