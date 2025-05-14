@section('js')
    <script nonce="{{ csp_nonce() }}"  >
    document.addEventListener("DOMContentLoaded", function(event) {
        $('.select2').select2({
            // theme: 'bootstrap4',
        });
    })
    </script>
@endsection