@include('components.wilayah_filter')
@push('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {

            $('#bt_clear_filter').click(function() {
                $('#filter_kabupaten').val(null).trigger("change");
                $('#bt_filter').click();
                $('#bt_clear_filter').hide();
            });

            $('#bt_filter').click(function() {
                $('#bt_clear_filter').show();
            });

            $('#bt_clear_filter').hide();
        })
    </script>
@endpush
